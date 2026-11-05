<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Password | QECH Attendance System</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f1f5f9;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 100%;
            max-width: 430px;
            padding: 35px;
            margin: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h2 {
            color: #14532d;
            margin-bottom: 8px;
        }

        .header p {
            color: #64748b;
            font-size: 14px;
        }

        label {
            display: block;
            margin: 15px 0 7px;
            font-weight: bold;
            color: #334155;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 25px;
            background: #15803d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #166534;
        }

        .error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>QECH Attendance System</h2>
        <p>Create your account password</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $email) }}"
            required
            autocomplete="email"
        >
        @error('email')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password">New Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
            minlength="8"
            autocomplete="new-password"
        >
        @error('password')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="password_confirmation">Confirm Password</label>
        <input
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            required
            minlength="8"
            autocomplete="new-password"
        >

        <button type="submit">Set My Password</button>
    </form>

    <div class="footer">
        Queen Elizabeth Central Hospital<br>
        Automated Account Setup
    </div>

</div>

</body>
</html>