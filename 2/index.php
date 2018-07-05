<?php

ini_set('display_errors', true);

class DB {
    private $connection;

    public function __construct(string $dsn, string $user, string $password) {
        $this->connection = new PDO($dsn, $user, $password);
    }

    public function save(Model $model) {
        $stmt = $this->connection->prepare("INSERT INTO `thedata` (`name`, `email`, `text`) VALUES (:name, :email, :text)");
        $stmt->execute([
            ':name' => $model->getData('name'),
            ':email' => $model->getData('email'),
            ':text' => $model->getData('text'),
        ]);
    }

    public function countComments() {
        $stmt = $this->connection->prepare("SELECT count(*) FROM `thedata`");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}

class Validator {
    private function validateRequired($value) {
        if (empty($value)) {
            return 'Обязательное поле';
        }
        return null;
    }

    private function validateMaxlength($value, $maxLength) {
        if (mb_strlen($value, 'UTF-8') > $maxLength) {
            return 'Максимальная длина поля: ' . $maxLength;
        }
        return null;
    }

    /**
     * Склонен верить данному посту: https://habr.com/post/320572/ - в том смысле, что провалидировать
     * адрес непросто и это не дает гарантии верного ввода.
     * Валидировать нет смысла, надо слать письмо активации и ждать клика по ссылке в письме.
     *
     * @param $value
     * @return null|string
     */
    private function validateEmail($value) {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return 'Некорректный e-mail';
        }
        return null;
    }

    public function validate($value, $rule, $ruleParam = null) {
        $validateFunc = 'validate' . ucfirst($rule);
        if (method_exists($this, $validateFunc)) {
            return $this->$validateFunc($value, $ruleParam);
        }
        throw new Exception("Unknown validation rule");
    }
}

class Model {
    private $data = [];

    private $attrbuteNames = ['name', 'email', 'text'];
    private $rules = [
        'name' => ['required', 'maxlength' => 100],
        'email' => ['required', 'email', 'maxlength' => 100],
        'text' => ['required', 'maxlength' => 1024],
    ];
    private $errors = [];

    public function load(array $postData) {
        foreach ($this->attrbuteNames as $attributeName) {
            $this->data[$attributeName] = $postData[$attributeName] ?? null;
        }
    }

    public function isValid() {
        $this->errors = [];
        $validator = new Validator();
        foreach ($this->attrbuteNames as $attributeName) {
            $rules = $this->rules[$attributeName] ?? [];
            $value = $this->getData($attributeName);
            foreach ($rules as $ruleKey => $ruleValue) {
                list($rule, $ruleParam) = is_string($ruleKey) ? [$ruleKey, $ruleValue] : [$ruleValue, null];
                $error = $validator->validate($value, $rule, $ruleParam);
                if ($error) {
                    $this->addError($attributeName, $error);
                }
            }
        }
        return empty($this->errors);
    }
    
    public function addError($attr, $message) {
        if (!isset($this->errors[$attr])) {
            $this->errors[$attr] = [];
        }
        $this->errors[$attr][] = $message;
    }

    public function getAllErrors() {
        return $this->errors;
    }
    
    public function getErrors($attr) {
        return $this->errors[$attr] ?? [];
    }

    public function getAttributeNames() {
        return $this->attrbuteNames;
    }

    public function getData($attr) {
        return $this->data[$attr] ?? null;
    }
}

class View {
    private $template;
    private $values;
    private $model;

    public function __construct(string $template, $values, Model $model) {
        $this->template = $template;
        $this->values = $values;
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function render() {
        $html = file_get_contents($this->template);
        foreach ($this->values as $name => $value) {
            $placeholder = '{{ ' . $name . ' }}';
            $html = str_replace($placeholder, $value, $html);
        }

        foreach ($this->model->getAttributeNames() as $attr) {
            $valuePlaceholder = '{{ ' . $attr . '.value }}';
            $value = $this->model->getData($attr);
            $html = str_replace($valuePlaceholder, htmlspecialchars($value), $html);

            $errorPlaceholder = '{{ ' . $attr . '.error }}';
            $error = implode('<br>', $this->model->getErrors($attr));
            $html = str_replace($errorPlaceholder, $error, $html);
        }
        return $html;
    }
}

// Вместо контроллера.

$connection = new DB("mysql:host=localhost;port=3306;dbname=smartleads", "smartleads", "smartleads");

session_start();
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
else {
    $flash = '';
}

$model = new Model();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model->load($_POST);
    if ($model->isValid()) {
        $connection->save($model);
        $_SESSION['flash'] = 'Данные сохранены!';
        header("Location: /");
        exit;
    }
    else {
        $flash = 'Ошибка!';
    }
}

$viewData = [
    'flash' => $flash,
    'comments' => $connection->countComments()
];

$view = new View('template.tpl', $viewData, $model);
echo $view->render();