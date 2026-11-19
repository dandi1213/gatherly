<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiket {{ $registration->event->title }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#4f46e5;padding:20px 24px;color:#ffffff;">
                            <div style="font-size:20px;font-weight:bold;">Gatherly</div>
                            <div style="font-size:13px;opacity:.9;">Konfirmasi registrasi event</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 8px;">Halo <strong>{{ $registration->attendee_name }}</strong>,</p>
                            <p style="margin:0 0 20px;line-height:1.6;">
                                Registrasi Anda berhasil. Berikut tiket digital untuk
                                <strong>{{ $registration->event->title }}</strong>.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <div style="font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;">Nomor Registrasi</div>
                                        <div style="font-size:16px;font-weight:bold;margin-bottom:12px;">{{ $registration->registration_number }}</div>
                                        <div style="font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;">Tipe Tiket</div>
                                        <div style="font-size:16px;font-weight:bold;">{{ $registration->ticketType->name }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 20px 16px;font-size:14px;color:#374151;">
                                        {{ $registration->event->start_at->locale('id')->translatedFormat('l, d F Y H:i') }}
                                        @if ($registration->event->location)
                                            <br>{{ $registration->event->location }}
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <div style="text-align:center;margin:8px 0 20px;">
                                <img src="cid:ticket-qr.png@gatherly" alt="QR Tiket" width="220" height="220" style="display:inline-block;border:1px solid #e5e7eb;border-radius:8px;padding:8px;background:#ffffff;">
                                <div style="font-size:12px;color:#6b7280;margin-top:8px;">Tunjukkan QR ini kepada staff saat check-in.</div>
                            </div>

                            <p style="text-align:center;margin:0 0 8px;">
                                <a href="{{ $ticketUrl }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:bold;">
                                    Lihat Tiket
                                </a>
                            </p>
                            <p style="text-align:center;font-size:12px;color:#6b7280;margin:8px 0 0;">
                                Anda perlu login untuk membuka halaman tiket.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;background:#f9fafb;font-size:12px;color:#6b7280;text-align:center;">
                            Email ini dikirim otomatis oleh Gatherly. Mohon jangan membalas email ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
