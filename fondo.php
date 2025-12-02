<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fondo Interactivo Profesional</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r121/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta/dist/vanta.net.min.js"></script>
<style>
  body, html {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Poppins', sans-serif;
    overflow: hidden;
    color: #fff;
  }
  #vanta-bg {
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: -1;
  }
  .content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding-top: 100px;
  }
  .content h1 {
    font-size: 3em;
    font-weight: 600;
  }
  .content p {
    font-size: 1.5em;
    max-width: 600px;
    margin: 20px auto 0 auto;
  }
  button.cta {
    margin-top: 30px;
    padding: 15px 30px;
    font-size: 1.2em;
    border: none;
    border-radius: 8px;
    background-color: #10B981; /* verde confianza */
    color: white;
    cursor: pointer;
    transition: 0.3s;
  }
  button.cta:hover {
    background-color: #059669;
  }
</style>
</head>
<body>

<div id="vanta-bg"></div>



<script>
  VANTA.NET({
    el: "#vanta-bg",
    mouseControls: true,
    touchControls: true,
    minHeight: 200.00,
    minWidth: 200.00,
    scale: 1.0,
    scaleMobile: 1.0,
    color: 0x3b82f6,        // azul serio
    backgroundColor: 0xf5f5f5, // gris claro para confianza
    points: 12.0,
    maxDistance: 25.0,
    spacing: 20.0,
  })
</script>

</body>
</html>
