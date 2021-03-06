<?php
// Desenvolvido por Joel - WHMCS.RED || Modificações de search inteligente feita por Luciano - WHMCS.RED
// Pegar Session
use WHMCS\Session;
// Pegar Conexão com Banco de Dados
use WHMCS\Database\Capsule;
// Bloqueia o acesso direto ao arquivo
if (!defined("WHMCS")){
	die("Acesso restrito!");
}
// Monta o PIN
function montar_pin($id){
	$limite = 5;
	$montar = md5($id);
	$montar = preg_replace("/[^0-9]/", "", $montar);
	$resultado = substr($montar, $limite, $limite);
	return $resultado;
}
// Página de Administrador
add_hook("AdminAreaClientSummaryPage", 1, function($vars){
	return "<div class='alert alert-success'><strong>PIN: ".montar_pin($vars["userid"])."</strong></div>";
});
// Página do Cliente
add_hook("ClientAreaHomepage", 2, function($vars){
	return "<div class='alert alert-success'><strong>PIN: ".montar_pin($_SESSION["uid"])."</strong></div>";
});
// Adicionando função de pesquisa do PIN
add_hook("IntelligentSearch", 1, function($vars){
	$pesquisa = array();
	foreach (capsule::table("tblclients")->get() as $clientes){
		$resultado = montar_pin($clientes->id);
		if($resultado == $vars["searchTerm"]){
			$idcliente = $clientes->id;
			$pin = $resultado;
		}
	}
	foreach (capsule::table("tblclients")->WHERE("id", $idcliente)->get() as $cliente){
		$pesquisa[] = '
		<div class="searchresult">
			<a href="clientssummary.php?userid='.$cliente->id.'">
				<strong>'.$cliente->firstname.' '.$cliente->lastname.'</strong>
				(PIN: '.$pin.')<br />
				<span class="desc">' . $cliente->email . '</span>
			</a>
		</div>';
	}
	return $pesquisa;
});
// Adiciona string para os templates de email
add_hook("EmailPreSend", 1, function($vars){
	$pinstring = array();
	$pinstring["pin"] = montar_pin($vars["userid"]);
	return $pinstring;
});
