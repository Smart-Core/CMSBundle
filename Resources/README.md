# Описание ресурсов приложения

## FMElfinderBundle

`@FMElfinder/Elfinder/helper/_tinymce4.html.twig`

Здесь сделано добавление / в начале пути в выбранному файлу т.к. по учолчанию вставляется относительный путь.

Также преобразование \ в / это актуально на веб-серверах под Windows.

## FOSUserBundle

`@FOSUser/Profile/show.html.twig`

Добавлены ссылки на редактирование профиля и изменение пароля.

`@FOSUser/Resetting/request_content.html.twig`

Пока необходимо вручную указывать _node_id в форме.

`@FOSUser/Security/login.html.twig`

Атрибут action="login_check" в форме сделан относительным т.к. используется в разных контекстах файрвола, а также добавлены ссылки на регистрацию и восстановление пароля.

## StfalconTinymceBundle

`@StfalconTinymce/Script/init.html.twig`

Подключение свежей версии Tinymce из FelibBundle, а также автофокус редактора.


#### Остальные переопределения

Все остальные переопределения шаблонов являются тестовыми и могут быть удалены из песочницы.
