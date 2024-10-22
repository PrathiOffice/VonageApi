<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email Address</title>
</head>
<body>
    <h1>Verify Your Email Address</h1>
    <p>
        Before proceeding, please check your email for a verification link.
        If you did not receive the email,
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">click here to request another</button>.
        </form>
    </p>
</body>
</html>
