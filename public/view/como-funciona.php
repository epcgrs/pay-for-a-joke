<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Pague por uma Piada</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Pague por uma piada com Bitcoin Lightning.">

  <!--
    Use Subresource Integrity (SRI) when loading third‑party scripts.  The integrity
    attribute allows the browser to verify that the downloaded file has not
    been tampered with.  The following integrity hash corresponds to
    jQuery 3.6.0 and can be found on the DataTables forums【626218495597209†L54-L59】.
  -->
  <script
    src="https://code.jquery.com/jquery-3.6.0.min.js"
    integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
    crossorigin="anonymous"></script>

  <!-- Preconnect to Google Fonts and include the Unkempt family.
       If possible, host fonts locally or provide SRI hashes for the font CSS. -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Unkempt:wght@400;700&display=swap" rel="stylesheet">

  <style>
    body { font-family: "Unkempt", sans-serif; letter-spacing: 1px; text-align: center; padding: 50px; background-color: #444; color: #fff; font-weight: 600; }
    button { padding: 10px 20px; margin-top: 20px; font-size: 1.2em; font-family: "Unkempt", sans-serif; border: none; border-radius: 5px; background-color: rgb(5, 163, 11); color: white; cursor: pointer; font-weight: 900; }
    button:hover { background-color: rgb(48, 138, 13); }
    label { display: block; margin-bottom: 20px; font-size: 1.2em; font-family: "Unkempt", sans-serif; }
    #paymentForm { display: flex; flex-direction: column; align-items: center; margin-top: 60px; }
    .amount { padding: 10px; font-size: 1.2em; font-family: "Unkempt", sans-serif; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 20px; }
    .piada { display: none; margin-top: 20px; font-weight: bold; padding: 30px; max-width: 640px; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin: auto; }
    .piadaAI { display: none; margin-top: 20px; font-weight: bold; padding: 30px; max-width: 640px; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin: auto; }
    .payment { display: none; margin-top: 20px; padding: 30px; max-width: 640px; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin: auto; }
    .payment .qrcode { text-align: center; }
    .payment .qrcode img { max-width: 100%; height: auto; }
    .payment .lninvoice { margin-top: 20px; font-size: 1.2em; word-break: break-all; font-family: sans-serif; }
    .error { color: red; margin-top: 20px; }
    .loading { display: none; text-align: center; margin-top: 50px; margin-bottom: 50px; }
    .success { display: none; text-align: center; margin-top: 50px; margin-bottom: 50px; }
    .success p { font-size: 1.5em; font-family: "Unkempt", sans-serif; }
    .ebook { display: none; margin-top: 50px; padding: 30px; max-width: 640px; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin: auto; }
    .ebook a { text-decoration: none; color: white; font-size: 1.2em; font-family: "Unkempt", sans-serif; background-color: rgb(5, 163, 11); padding: 10px; border-radius: 5px; }
    .ebook a:hover { background-color: rgb(48, 138, 13); }
    .back { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: rgb(5, 163, 11); color: white; text-decoration: none; font-size: 1.2em; font-family: "Unkempt", sans-serif; border-radius: 5px; }
    .back:hover { background-color: rgb(48, 138, 13); }
  </style>

  <!-- Optional: Content Security Policy.  Adjust the directives to suit your needs.
       This basic policy restricts all resources to the same origin except for
       scripts from code.jquery.com, stylesheets from Google Fonts, images from
       api.qrserver.com and media.giphy.com.
       Note: Test CSP thoroughly in your environment to ensure that legitimate
       functionality is not blocked. -->
<?php
  $CSP_CONNECT = $_ENV['CSP_CONNECT_SRC'] ?? "'self'";
  $CSP_IMG     = $_ENV['CSP_IMG_SRC'] ?? "'self' https://api.qrserver.com https://media.giphy.com";
  $CSP_SCRIPT  = $_ENV['CSP_SCRIPT_SRC'] ?? "'self' https://code.jquery.com";
  $CSP_STYLE   = $_ENV['CSP_STYLE_SRC'] ?? "'self' 'unsafe-inline' https://fonts.googleapis.com";
  $CSP_FONT    = $_ENV['CSP_FONT_SRC'] ?? 'https://fonts.gstatic.com';
?>
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; connect-src <?php echo htmlspecialchars($CSP_CONNECT, ENT_QUOTES); ?>; img-src <?php echo htmlspecialchars($CSP_IMG, ENT_QUOTES); ?>; script-src <?php echo htmlspecialchars($CSP_SCRIPT, ENT_QUOTES); ?>; style-src <?php echo htmlspecialchars($CSP_STYLE, ENT_QUOTES); ?>; font-src <?php echo htmlspecialchars($CSP_FONT, ENT_QUOTES); ?>">
</head>
<body>
    <main>
        <h1>Como Funciona</h1>
        <p>Para pagar por uma piada, você precisa enviar uma quantia em Satoshis (a menor unidade do Bitcoin) através da rede Lightning.</p>
        <p>Após o pagamento, você receberá uma piada aleatória e uma piada gerada por IA.</p>
        <p>Se o valor pago for maior que 2000 ou mais Satoshis, você receberá um presente pela sua quantia, um E-book de Bitcoin do zero ao avançado.</p>
        <p>Divirta-se e compartilhe com seus amigos!</p>
        <a href='/' class="back">Voltar para a Página Inicial</a>
    </main>
</body>
</html>