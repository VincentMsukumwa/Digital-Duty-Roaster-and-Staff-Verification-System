<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>QECH Account Created</title>
</head>
<body style="margin:0; padding:30px; background:#f4f6f9; font-family:Arial,sans-serif;">

    <div style="max-width:600px; margin:auto; background:white; border-radius:8px; overflow:hidden;">

        <div style="background:#14532d; color:white; padding:25px; text-align:center;">
            <h2 style="margin:0;">QECH Attendance System</h2>
            <p style="margin:8px 0 0;">Staff Account Notification</p>
        </div>

        <div style="padding:30px; color:#333;">

            <h3>Hello {{ $user->username }},</h3>

            <p>
                Your user account for the Queen Elizabeth Central Hospital
                Attendance System has been successfully created.
            </p>

            <p><strong>Your account details:</strong></p>

            <p>
                Username: <strong>{{ $user->username }}</strong><br>
                Email: <strong>{{ $user->email }}</strong>
            </p>

            <p>
                To activate your account, please click the button below
                to create your password.
            </p>

            <div style="text-align:center; margin:30px 0;">
                <a href="{{ $resetUrl }}"
                   style="background:#15803d; color:white; padding:14px 25px;
                          text-decoration:none; border-radius:5px;
                          display:inline-block; font-weight:bold;">
                    Set Up My Password
                </a>
            </div>

            <p>
                For security reasons, this password setup link expires after
                the configured validity period.
            </p>

            <p>
                If you were not expecting this account, please contact
                the QECH system administrator.
            </p>

            <p>Regards,<br>
                <strong>QECH Attendance System</strong>
            </p>

        </div>

        <div style="background:#f1f5f9; padding:15px; text-align:center;
                    font-size:12px; color:#64748b;">
            This is an automated email. Please do not reply.
        </div>

    </div>

</body>
</html>