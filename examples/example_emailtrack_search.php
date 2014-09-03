<?php
/**
 * emailtrack_search
 * Exemplo de como gerar um relatório de envio de e-mails do servidor
 *
 */

 // include do autoload
include '../vendor/autoload.php';

use MJP\VHMAuth;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
<title>Documentação da Abas Cadastro</title>
</head>
<body>
<div class="container bs-docs-container">
    <div class="row">
        <div class="col-md-12" role="main">
<?php
/*
 Url de conexão do VHM
 exemplo com url segura     [https://DOMINIO_DO_SEU_SERVIDOR:2087/json-api/]
 exemplo com url não segura [http://DOMINIO_DO_SEU_SERVIDOR:2086/json-api/]
**/
$urlVhm = '';

// Usuário válido no VHM
$usrVhm = '';

// Hash gerada no painel do VHM [vide: http://docs.cpanel.net/twiki/bin/view/AllDocumentation/WHMDocs/RemoteAccess]
$hshVhm = '';

// Cria o objeto
$authVhm = new VHMAuth($urlVhm,$usrVhm,$hshVhm);

// Gera o relatório de e-mails
$json = $authVhm->emailtrack_search(1, 1, 1, 1, 'all', 'actiontime', 1, 'sender', 'begins', 'UM_VALOR_QUALQUER', '2014/09/02 10:42:07', '2014/09/03 18:00:01', 1, 5000);
?>
                <h1>Var dump do resultado</h1>
                <?php var_dump($json); ?>

                <hr /><br />

                <h2>Resultado</h2>
                <hr />

<?php
    $arrRetorno = json_decode($json, true);

    $authVhm->debug($arrRetorno);
?>
        </div>
    </div>
</div>
<!-- Latest compiled and minified JavaScript -->
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</body>
</html>
