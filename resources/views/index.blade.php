<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }}</title>

    <style>
        code {
            border: 1px solid black;
            padding: 5px
        }

    </style>
</head>

<body>
    <p>Hello, {{ $user->name }}</p>
    <p>你的 Token。Your Token.</p>
    <code>{{ $user->api_token }}</code>

    <p>
        复制并粘贴到应用程序中即可使用。
        Copy and paste to applications.
    </p>

    @env('local')
    <p style="color: #ff7171">
        你现在正在测试环境中，Token 是当前登录用户的邮箱。
        <br />
        在部署应用程序时请切记调整应用程序运行环境，否则会有意想不到的后果。
    </p>
    @endenv

    <p>You can also request token using <a href="?format=json">json format</a> or <a href="?format=plaintext">plaintext</a> </p>
</body>

</html>
