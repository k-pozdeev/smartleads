<?php

class FileSeeker implements SeekableIterator {
    const CHUNK_SIZE = 1024 * 1024;

    private $filename;
    private $filesize;
    private $handle;
    private $position;

    /**
     * Массив, в который для записи под номером i сохраняется номер позиции ее последнего символа в файле.
     * Записи нумеруются от нуля.
     */
    private $boundaries = [];

    /**
     * FileSeeker constructor.
     * @param $filename
     * @throws Exception
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->filesize = filesize($filename);
        if ($this->filesize == 0) {
            throw new Exception("File must contain some data");
        }
        $this->handle = fopen($filename, 'r');
        $this->position = 0;
        $this->seekAndFillRemainingBoundariesUpTo($this->position);
    }

    private function getBoundariesOfRecord(int $position) {
        $beginpos = ($this->position == 0) ? 0 : $this->boundaries[$this->position - 1] + 1;
        $endpos = $this->boundaries[$position];
        return [$beginpos, $endpos];
    }

    public function current()
    {
        list($beginpos, $endpos) = $this->getBoundariesOfRecord($this->position);
        fseek($this->handle, $beginpos);
        $output = fread($this->handle, $endpos - $beginpos + 1);
        if (strlen($output) != $endpos - $beginpos + 1) {
            throw new Exception("Invalid read from file");
        }
        return $output;
    }

    public function next()
    {
        $this->position++;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        if (isset($this->boundaries[$this->position])) {
            return true;
        }
        if ($this->allBoundariesFilled() == false) {
            $this->seekAndFillRemainingBoundariesUpTo($this->position);
            if (isset($this->boundaries[$this->position])) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function seek($position)
    {
        if ($position < 0) {
            throw new OutOfBoundsException("Invalid position: " . $position);
        }
        if (isset($this->boundaries[$position])) {
            $this->position = $position;
            return;
        }
        if ($this->allBoundariesFilled() == false) {
            $this->seekAndFillRemainingBoundariesUpTo($position);
        }
        $knownLastPosition = count($this->boundaries) - 1;
        if ($position > $knownLastPosition) {
            throw new OutOfBoundsException("Invalid position: " . $position);
        }
        $this->position = $position;
    }

    private function allBoundariesFilled() {
        if (count($this->boundaries) == 0) return false;
        $knownLastPosition = count($this->boundaries) - 1;
        return $this->boundaries[$knownLastPosition] == $this->filesize - 1;
    }

    private function seekAndFillRemainingBoundariesUpTo(int $positionToSeek) {
        $knownLastPosition = count($this->boundaries) - 1;
        $offset = $knownLastPosition < 0 ? 0 : $this->boundaries[$knownLastPosition] + 1;
        fseek($this->handle, $offset);
        while (true) {
            $chunk = fread($this->handle, self::CHUNK_SIZE);
            if ($chunk === false) {
                break;
            }
            $searchOffset = 0;
            while (true) {
                $pos = strpos($chunk, "\n", $searchOffset);
                if ($pos === false) {
                    break;
                }
                $knownLastPosition++;
                $this->boundaries[$knownLastPosition] = $offset + $pos;
                $searchOffset = $pos + 1;
            }
            $offset += strlen($chunk);
            if ($offset >= $this->filesize) {
                if ($this->boundaries[$knownLastPosition] < $this->filesize - 1) {
                    $knownLastPosition++;
                    $this->boundaries[$knownLastPosition] = $this->filesize - 1;
                }
                break;
            }
            if ($positionToSeek <= $knownLastPosition) {
                return;
            }
        }
    }

    public function __destruct()
    {
        fclose($this->handle);
    }
}

$fileSeeker = new FileSeeker('text.txt');
for ($i = 0; $i < 10000; $i++) {
    $position = rand(0, 25000000);
    $fileSeeker->seek($position);
    echo $i . '; ' . $position . '; ' . $fileSeeker->current() . "\n";
}