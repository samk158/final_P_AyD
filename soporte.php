<?php include("barra_sup.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Soporte Profesional - Mi Tienda</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background: #f1f5f9;
    }
    /* Barra superior ya incluida */

    .header {
        background: #1E3A8A;
        color: #fff;
        padding: 20px;
        text-align: center;
        font-size: 28px;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 999;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    .contenedor {
        max-width: 900px;
        margin: 30px auto;
        padding: 20px;
    }

    .contactos {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .contacto {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: transform 0.2s;
    }

    .contacto:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }

    .contacto a {
        margin-top: 10px;
        text-decoration: none;
        color: #1E3A8A;
        font-weight: bold;
        font-size: 18px;
    }

    .contacto a:hover {
        color: #2563EB;
    }

    .faqs {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .faq-item {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .faq-title {
        background: #2563EB;
        color: #fff;
        padding: 15px 20px;
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .faq-content {
        max-height: 0;
        overflow: hidden;
        padding: 0 20px;
        background: #f9fafe;
        transition: max-height 0.4s ease;
    }

    .faq-content p {
        padding: 15px 0;
        margin: 0;
        color: #1E3A8A;
    }

    .faq-title::after {
        content: '\25BC';
        transition: transform 0.3s;
    }

    .faq-item.active .faq-title::after {
        transform: rotate(180deg);
    }
</style>
</head>
<body>
<div class="header">Soporte al Cliente - Mi Tienda</div>
<div class="contenedor">
    <h2 style="color:#1E3A8A; margin-bottom:20px;">Contáctanos y resuelve tus dudas</h2>
    <div class="contactos">
        <div class="contacto">
            <strong>💬 WhatsApp</strong>
            <a href="https://wa.me/59178843348" target="_blank">+591 78843348</a>
        </div>
        <div class="contacto">
            <strong>📧 Correo</strong>
            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=soporte@mitienda.com" target="_blank">soporte@mitienda.com</a>
        </div>
    </div>

    <h2 style="color:#1E3A8A; margin-bottom:20px;">Preguntas frecuentes</h2>
    <div class="faqs">
        <div class="faq-item">
            <div class="faq-title">📦 Mi pedido no llegó o está retrasado</div>
            <div class="faq-content"><p>Los retrasos pueden deberse al clima, tráfico o alta demanda. Contáctanos por WhatsApp para verificar el estado de tu pedido.</p></div>
        </div>
        <div class="faq-item">
            <div class="faq-title">🍎 Recibí productos en mal estado</div>
            <div class="faq-content"><p>Envía una foto del producto por WhatsApp y recibes reemplazo o reembolso inmediato.</p></div>
        </div>
        <div class="faq-item">
            <div class="faq-title">💳 Problemas con el pago</div>
            <div class="faq-content"><p>Si tu pago no se registró, puedes reenviar comprobante o contactarnos para validarlo.</p></div>
        </div>
        <div class="faq-item">
            <div class="faq-title">🛒 Modificar o cancelar pedido</div>
            <div class="faq-content"><p>Modificaciones solo en los primeros 5 minutos. Contacta de inmediato por WhatsApp.</p></div>
        </div>
        <div class="faq-item">
            <div class="faq-title">📍 Problemas con mi ubicación</div>
            <div class="faq-content"><p>Si tu zona no aparece, envíanos la ubicación por WhatsApp y procesamos tu pedido manualmente.</p></div>
        </div>
        <div class="faq-item">
            <div class="faq-title">🧾 Solicitar factura</div>
            <div class="faq-content"><p>Envía tus datos de facturación por WhatsApp para generar la factura.</p></div>
        </div>
        <div class="faq-item">
            <div class="faq-title">🔐 No puedo iniciar sesión</div>
            <div class="faq-content"><p>Solicita restaurar tu cuenta o cambiar contraseña por WhatsApp o correo.</p></div>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('.faq-title').forEach(title => {
        title.addEventListener('click', () => {
            const item = title.parentElement;
            item.classList.toggle('active');
            const content = title.nextElementSibling;
            if(item.classList.contains('active')){
                content.style.maxHeight = content.scrollHeight + 'px';
            } else {
                content.style.maxHeight = null;
            }
        });
    });
</script>
</body>
</html>