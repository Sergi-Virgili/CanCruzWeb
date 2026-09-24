<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva confirmada</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #10b981;">Tu reserva ha sido confirmada</h1>

    <p>Hola <strong>{{ e($reservation->name) }}</strong>,</p>

    <p>Nos complace informarte que tu reserva ha sido <strong style="color: #10b981;">confirmada</strong>. Aquí tienes los detalles:</p>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background-color: #f9fafb;">Fecha de entrada</td>
            <td style="padding: 10px; border: 1px solid #ddd;">{{ e($reservation->entryDate) }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #ddd; font-weight: bold; background-color: #f9fafb;">Fecha de salida</td>
            <td style="padding: 10px; border: 1px solid #ddd;">{{ e($reservation->outDate) }}</td>
        </tr>
    </table>

    <p>¡Esperamos verte pronto!</p>

    <p>Si tienes alguna pregunta, no dudes en responder a este correo.</p>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

    <p style="font-size: 0.875rem; color: #6b7280;">
        Este es un mensaje automático, por favor no respondas directamente a esta dirección.
    </p>
</body>
</html>
