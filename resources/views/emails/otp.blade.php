<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi PawCheck</title>
</head>
<body style="margin:0; padding:0; background-color:#FFF8F0; font-family: Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#FFF8F0; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#FFFFFF; border-radius:12px; overflow:hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color:#FF6B6B; padding: 28px 32px; text-align:center;">
                            <h1 style="margin:0; color:#FFFFFF; font-size:22px; font-weight:700;">🐾 PawCheck</h1>
                            <p style="margin:8px 0 0; color:#FFE0E0; font-size:13px;">Verifikasi Email Anda</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin:0 0 8px; color:#3D3D3D; font-size:15px;">Halo, <strong>{{ $userName }}</strong>!</p>
                            <p style="margin:0 0 24px; color:#6B6B6B; font-size:14px; line-height:1.6;">
                                Terima kasih telah mendaftar di <strong>PawCheck</strong>. Gunakan kode OTP berikut untuk memverifikasi akun Anda:
                            </p>
                            <div style="text-align:center; margin: 24px 0;">
                                <div style="display:inline-block; background:#FFF0F0; border: 2px dashed #FF6B6B; border-radius:12px; padding: 18px 40px;">
                                    <span style="font-size:40px; font-weight:800; letter-spacing:12px; color:#FF6B6B;">{{ $otp }}</span>
                                </div>
                            </div>
                            <p style="margin:0 0 8px; color:#6B6B6B; font-size:14px; text-align:center;">
                                Kode ini berlaku selama <strong>10 menit</strong>.
                            </p>
                            <hr style="border:none; border-top:1px solid #F0E0D0; margin: 24px 0;">
                            <p style="margin:0; color:#9E9E9E; font-size:12px; text-align:center; line-height:1.5;">
                                Jika Anda tidak mendaftar di PawCheck, abaikan email ini.<br>
                                Jangan bagikan kode OTP ini kepada siapapun.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#FFF0E0; padding:14px 32px; text-align:center;">
                            <p style="margin:0; color:#9E9E9E; font-size:11px;">© 2025 PawCheck — Aplikasi Pemantau Kesehatan Hewan Peliharaan</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
