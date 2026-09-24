<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Hemos recibido tu solicitud</title>
</head>
<body style="margin: 0; padding: 0; background-color: #eee9e1; font-family: Arial, Helvetica, sans-serif; color: #30342f;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #eee9e1;">
        <tr>
            <td align="center" style="padding-top: 28px; padding-right: 16px; padding-bottom: 28px; padding-left: 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; background-color: #fffdf9;">
                    <tr>
                        <td style="padding-top: 32px; padding-right: 36px; padding-bottom: 30px; padding-left: 36px; background-color: #e9dfd2;">
                            <p style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 21px; line-height: 28px; color: #4c5948;">Masia Can Cruz</p>
                            <p style="margin-top: 38px; margin-bottom: 0; font-size: 11px; line-height: 16px; letter-spacing: 2px; text-transform: uppercase; color: #a86f50;">Solicitud recibida</p>
                            <h1 style="margin-top: 10px; margin-bottom: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 32px; line-height: 36px; font-weight: normal; color: #30342f;">Una casa con raíces.<br>Un lugar para estar juntos.</h1>
                            <p style="margin-top: 18px; margin-bottom: 0; font-size: 16px; line-height: 25px; color: #62665e;">Hemos recibido tu solicitud y guardamos estas fechas mientras revisamos la disponibilidad.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 30px; padding-right: 36px; padding-bottom: 30px; padding-left: 36px;">
                            <p style="margin-top: 0; margin-bottom: 16px; font-size: 16px; line-height: 25px; color: #30342f;">Hola <strong>{{ e($reservation->name) }}</strong>,</p>
                            <p style="margin-top: 0; margin-bottom: 22px; font-size: 15px; line-height: 24px; color: #62665e;">Gracias por contactar con nosotros. Te confirmaremos la disponibilidad lo antes posible.</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top: 1px solid #d8d0c4; border-bottom: 1px solid #d8d0c4;">
                                <tr>
                                    <td style="padding-top: 16px; padding-bottom: 7px; font-size: 13px; line-height: 19px; color: #7c8078;">Entrada</td>
                                    <td align="right" style="padding-top: 16px; padding-bottom: 7px; font-size: 13px; line-height: 19px; font-weight: bold; color: #30342f;">{{ e($reservation->entryDate) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 7px; padding-bottom: 16px; font-size: 13px; line-height: 19px; color: #7c8078;">Salida</td>
                                    <td align="right" style="padding-top: 7px; padding-bottom: 16px; font-size: 13px; line-height: 19px; font-weight: bold; color: #30342f;">{{ e($reservation->outDate) }}</td>
                                </tr>
                            </table>
                            <p style="margin-top: 22px; margin-bottom: 0; padding-top: 11px; padding-right: 14px; padding-bottom: 11px; padding-left: 14px; background-color: #f5efe7; font-size: 13px; line-height: 20px; color: #62665e;"><strong style="color: #a86f50;">Pendiente de confirmación.</strong> Te escribiremos pronto con los siguientes pasos.</p>
                            <p style="margin-top: 26px; margin-bottom: 0; font-size: 13px; line-height: 20px; color: #7c8078;">Si tienes alguna pregunta, no dudes en responder a este correo.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 18px; padding-right: 36px; padding-bottom: 18px; padding-left: 36px; background-color: #f5f1eb;">
                            <p style="margin: 0; font-size: 11px; line-height: 17px; color: #81847b;">Masia Can Cruz · Parc Natural del Montseny<br>Este es un mensaje automático.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
