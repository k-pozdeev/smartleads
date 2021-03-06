<!DOCTYPE html>
<head>
    <title>Sample Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="#">Оставьте свой комментарий. Всего: {{ comments }}</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-0 col-md-4 col-lg-4"></div>
            <div class="col-sm-12 col-md-4 col-lg-4">
                <div clas="flash">{{ flash }}</div>
                <form name="theform" class="form form-horizontal" method="POST">
                    <div class="form-group">
                        <label for="name">Имя</label>
                        <input type="text" id="name" name="name" value="{{ name.value }}" class="form-control"/>
                        <div class="error">{{ name.error }}</div>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="text" id="email" name="email" value="{{ email.value }}" class="form-control"/>
                        <div class="error">{{ email.error }}</div>
                    </div>
                    <div class="form-group">
                        <label for="text">Текст</label>
                        <textarea id="text" name="text" class="form-control">{{ text.value }}</textarea>
                        <div class="error">{{ text.error }}</div>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Отправить"/>
                    </div>
                </form>
            </div>
            <div class="col-sm-0 col-md-4 col-lg-4"></div>
        </div>
    </div>
</body>
</html>