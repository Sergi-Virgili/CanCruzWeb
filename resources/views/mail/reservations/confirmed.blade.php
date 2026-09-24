<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tu reserva ha sido confirmada</title>
</head>
<body style="margin: 0; padding: 0; background-color: #e6e9e4; font-family: Arial, Helvetica, sans-serif; color: #ffffff;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #e6e9e4;">
        <tr>
            <td align="center" style="padding-top: 28px; padding-right: 16px; padding-bottom: 28px; padding-left: 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #2f4a3c;">
                    <tr>
                        <td style="padding-top: 32px; padding-right: 36px; padding-bottom: 34px; padding-left: 36px;">
                            <p style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 21px; line-height: 28px; color: #ffffff;">Masia Can Cruz</p>
                            <p style="margin-top: 44px; margin-bottom: 0; font-size: 11px; line-height: 16px; letter-spacing: 2px; text-transform: uppercase; color: #d7b497;">Reserva confirmada</p>
                            <h1 style="margin-top: 10px; margin-bottom: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 34px; line-height: 38px; font-weight: normal; color: #ffffff;">Tu estancia<br>está confirmada</h1>
                            <p style="margin-top: 20px; margin-bottom: 0; font-size: 16px; line-height: 25px; color: #d9e3dc;">Todo listo para disfrutar del Montseny. Aquí tienes las fechas de tu estancia.</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 28px; border-top: 1px solid #617b6b; border-bottom: 1px solid #617b6b;">
                                <tr>
                                    <td style="padding-top: 16px; padding-bottom: 7px; font-size: 13px; line-height: 19px; color: #bdd0c3;">Entrada</td>
                                    <td align="right" style="padding-top: 16px; padding-bottom: 7px; font-size: 13px; line-height: 19px; font-weight: bold; color: #ffffff;">{{ e($reservation->entryDate) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 7px; padding-bottom: 16px; font-size: 13px; line-height: 19px; color: #bdd0c3;">Salida</td>
                                    <td align="right" style="padding-top: 7px; padding-bottom: 16px; font-size: 13px; line-height: 19px; font-weight: bold; color: #ffffff;">{{ e($reservation->outDate) }}</td>
                                </tr>
                            </table>
                            <p style="margin-top: 24px; margin-bottom: 0; padding-top: 12px; padding-right: 16px; padding-bottom: 12px; padding-left: 16px; background-color: #3c5b4b; font-size: 13px; line-height: 20px; color: #d9e3dc;">Casa completa para compartir, descansar y disfrutar del entorno.</p>
                            <p style="margin-top: 28px; margin-bottom: 0; font-size: 16px; line-height: 25px; color: #ffffff;">¡Esperamos verte pronto!</p>
                            <p style="margin-top: 22px; margin-bottom: 0; font-size: 13px; line-height: 20px; color: #bdd0c3;">Si tienes alguna pregunta, no dudes en responder a este correo.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 18px; padding-right: 36px; padding-bottom: 18px; padding-left: 36px; background-color: #263d32;">
                            <p style="margin: 0; font-size: 11px; line-height: 17px; color: #bdd0c3;">Masia Can Cruz · Parc Natural del Montseny<br>Este es un mensaje automático.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
