<?php
//Data e Hora Time Zone Maputo Mozambique
$tz = 'Africa/Maputo';
$tz_obj = new DateTimeZone($tz);
$Agora = new DateTime("now", $tz_obj);
date_default_timezone_set('Africa/Maputo');

function AdicionaMail($Con, $ID)
{

    $BuscarExistencia = $Con->prepare("SELECT * FROM EnviarOrdem WHERE ID_Ordem=?");
    $BuscarExistencia->bindParam(1, $ID);
    $BuscarExistencia->execute();

    if ($BuscarExistencia->rowCount() <= 0)
    {
        $EstadoInicial = "PENDENTE";
        $InserirID = $Con->prepare("INSERT INTO EnviarOrdem (Estado, ID_Ordem) VALUES (?,?)");

        $InserirID->bindParam(1, $EstadoInicial);
        $InserirID->bindParam(2, $ID);
        $InserirID->execute();

    }
    else
    {

    }

}

function CarregarMail($Con, $ID, $Data, $Hora)
{

    $Cod = 1;
    $Estado = "ENVIADO";

    $Update = $Con->prepare("UPDATE EnviarOrdem SET Estado=?, Codigo_Estado=?, Data_Envio=?, Hora_Envio=? WHERE ID_Ordem=?");
    $Update->bindParam(1, $Estado);
    $Update->bindParam(2, $Cod);
    $Update->bindParam(3, $Data);
    $Update->bindParam(4, $Hora);
    $Update->bindParam(5, $ID);
    $Update->execute();

}

function ProjectoBase($Con, $ID)
{

    $Buscar = $Con->prepare("SELECT * FROM ProjectosBase WHERE ID_ProjectosBase=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    $Carregar = $Buscar->fetch(PDO::FETCH_OBJ);

    $NomeProjecto = $Carregar->Nome_ProjectosBase;

    return $NomeProjecto;

}

function RotaAbastecida($Conexao, $Rotas, $Dia, $Mes, $Ano)
{
    $RowC;

    foreach ($Rotas as $Roots)
    {

        $BuscarRow = $Conexao->prepare("SELECT * FROM Combustiveis WHERE Rota LIKE '%$Roots%' and Dia_Abastecimento=? and Mes_Abastecimento=? and Ano_Abastecimento=?");
        $BuscarRow->bindParam(1, $Dia);
        $BuscarRow->bindParam(2, $Mes);
        $BuscarRow->bindParam(3, $Ano);
        $BuscarRow->execute();

        $RowC += $BuscarRow->rowCount();

    }

    return $RowC;

}

function CarroAbastecido($Con, $Carro, $Data)
{
    $RowC;

    $Buscar = $Con->prepare("SELECT * FROM Combustiveis WHERE Matricula=? and Data_Abastecimento=?");
    $Buscar->bindParam(1, $Carro);
    $Buscar->bindParam(2, $Data);

    $Buscar->execute();

    $RowC = $Buscar->rowCount();

    return $RowC;

}

function KMAbastecer($Con, $Rotas)
{
    $Quilometrage;
    foreach ($Rotas as $Roots)
    {

        $BuscarKM = $Con->prepare("SELECT KmEST FROM roots WHERE Nome=?");
        $BuscarKM->bindParam(1, $Roots);
        $BuscarKM->execute();

        $PercorrerNow = $BuscarKM->fetch(PDO::FETCH_OBJ);

        $Quilometrage += $PercorrerNow->KmEST;

    }

    return $Quilometrage;

}

function MenorHora($Con, $Rotas)
{
    $HORA = "23:59:59";

    foreach ($Rotas as $Roots)
    {

        $BuscarKM = $Con->prepare("SELECT * FROM roots WHERE Nome=?");
        $BuscarKM->bindParam(1, $Roots);
        $BuscarKM->execute();

        $PercorrerNow = $BuscarKM->fetch(PDO::FETCH_OBJ);

        if (strtotime($PercorrerNow->Hora_Predifinida_Saida) < strtotime($HORA))
        {

            $HORA = $PercorrerNow->Hora_Predifinida_Saida;

        }
        else
        {

            $HORA = $HORA;
        }

    }

    return $HORA;

}

function MaiorHora($Con, $Rotas)
{
    $HORA = "00:00:00";

    foreach ($Rotas as $Roots)
    {

        $BuscarKM = $Con->prepare("SELECT * FROM roots WHERE Nome=?");
        $BuscarKM->bindParam(1, $Roots);
        $BuscarKM->execute();

        $PercorrerNow = $BuscarKM->fetch(PDO::FETCH_OBJ);

        if (strtotime($PercorrerNow->Hora_Predifinida_Chegada) > strtotime($HORA))
        {

            $HORA = $PercorrerNow->Hora_Predifinida_Chegada;

        }
        else
        {

            $HORA = $HORA;
        }

    }

    return $HORA;

}

function NomeRotas($Rotas)
{
    $Nomes;

    foreach ($Rotas as $Roots)
    {

        $Nomes .= $Roots . " | ";

    }

    return $Nomes;
}

function ActualizarNumeroOrdem($Con)
{

    $BuscarNumeroOrdem = $Con->prepare("SELECT * FROM Ordem");
    $BuscarNumeroOrdem->execute();

    $CarrgarNumero = $BuscarNumeroOrdem->fetch(PDO::FETCH_OBJ);

    $NumeroAntigo = $CarrgarNumero->Codigo_Ordem;

    $NovoNumero = $NumeroAntigo + 1;
    $ActualizarNumero = $Con->prepare("UPDATE Ordem SET Codigo_Ordem=?");
    $ActualizarNumero->bindParam(1, $NovoNumero);
    $ActualizarNumero->execute();

}

function BuscarNumeroOrdem($Con)
{

    $BuscarNumeroOrdem = $Con->prepare("SELECT * FROM Ordem");
    $BuscarNumeroOrdem->execute();

    $CarrgarNumero = $BuscarNumeroOrdem->fetch(PDO::FETCH_OBJ);

    $NumeroAntigo = $CarrgarNumero->Codigo_Ordem;

    return $NumeroAntigo;

}

function CalcularQuantidadeEstipulada($Con, $Matricula, $KM)
{

    $BuscarDadosViatura = $Con->prepare("SELECT * FROM viatura WHERE MAtricula=?");
    $BuscarDadosViatura->bindParam(1, $Matricula);
    $BuscarDadosViatura->execute();

    $Carregar = $BuscarDadosViatura->fetch(PDO::FETCH_OBJ);

    $Media = $Carregar->LitrosPorQuilometro;

    $QuantidadeAbastecer = $Media * $KM;

    return $QuantidadeAbastecer;

}

function DetalhesViatura($Con, $Matricula)
{

    $Buscar = $Con->prepare("SELECT * FROM viatura WHERE Matricula=?");
    $Buscar->bindParam(1, $Matricula);
    $Buscar->execute();

    $carregarDadosViatura = $Buscar->fetch(PDO::FETCH_OBJ);

    $TIPO = $carregarDadosViatura->TipodeCombustivel;
    $PrecoCombustivel;

    if ($TIPO == "GASOLINA")
    {

        $BuscaPreco = $Con->prepare("SELECT GASOLINA FROM CombustivelPreco");
        $BuscaPreco->execute();

        $CarregarPreco = $BuscaPreco->fetch(PDO::FETCH_OBJ);

        $PrecoCombustivel = $CarregarPreco->GASOLINA;

    }
    else
    {

        $BuscaPreco = $Con->prepare("SELECT DIESEL FROM CombustivelPreco");
        $BuscaPreco->execute();

        $CarregarPreco = $BuscaPreco->fetch(PDO::FETCH_OBJ);

        $PrecoCombustivel = $CarregarPreco->DIESEL;

    }
    $Ary = array(
        "Preco" => $PrecoCombustivel,
        "Marca" => $carregarDadosViatura->Marca,
        "TipoCombustivel" => $carregarDadosViatura->TipodeCombustivel,
        "Media" => $carregarDadosViatura->LitrosPorQuilometro
    );

    return $Ary;

}

function DetalhesViaturas($Con, $Matricula)
{

    $Buscar = $Con->prepare("SELECT * FROM viatura WHERE Matricula=?");
    $Buscar->bindParam(1, $Matricula);
    $Buscar->execute();

    $carregarDadosViatura = $Buscar->fetch(PDO::FETCH_OBJ);

    $TIPO = $carregarDadosViatura->TipodeCombustivel;
    $PrecoCombustivel;

    if ($TIPO == "GASOLINA")
    {

        $BuscaPreco = $Con->prepare("SELECT GASOLINA FROM CombustivelPreco");
        $BuscaPreco->execute();

        $CarregarPreco = $BuscaPreco->fetch(PDO::FETCH_OBJ);

        $PrecoCombustivel = $CarregarPreco->GASOLINA;

    }
    else
    {

        $BuscaPreco = $Con->prepare("SELECT DIESEL FROM CombustivelPreco");
        $BuscaPreco->execute();

        $CarregarPreco = $BuscaPreco->fetch(PDO::FETCH_OBJ);

        $PrecoCombustivel = $CarregarPreco->DIESEL;

    }
    $Ary = array(
        ["Preco" => $PrecoCombustivel,
        "Marca" => $carregarDadosViatura->Marca,
        "TipoCombustivel" => $carregarDadosViatura->TipodeCombustivel,
        "Media" => $carregarDadosViatura->LitrosPorQuilometro]
    );

    return $Ary;

}

function InserirAbastecimento($Con, $Pbase, $RotasString, $RotasJson, $Matricula, $Marca, $TipoCombustivel, $QuantidadeAbastecida, $PrecoUnitario, $valorPago, $QuantidadeTanque, $KMEstipulada, $QuantidadeEstipulada, $DataAbastecimento, $DiaAbastecimento, $MesAbastecimento, $AnoAbastecimento, $HoraAbastecimento, $IDOrdem, $Semana, $TipoAbastecimento, $Responsavel, $Bombas, $ContagemBomba)
{

    $Resposta;

    $Inserir = $Con->prepare("INSERT INTO Combustiveis(ProjectoBase, Rota, JsonRotas, Matricula, Marca, Tipo_Combustivel, Quantidade_Abastecida, Preco_Unitario, Valor_Pago, Quantidade_Tanque, Quilometragem_Estipulada, Quantidade_Estipulada, Data_Abastecimento, Dia_Abastecimento, Mes_Abastecimento, Ano_Abastecimento, Hora_Abastecimento, ID_Ordem_Abastecimento, Semana, Tipo_Abastecimento, AbastecidoPor,LocalAbastecimento, ContagemBomba) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    $Inserir->bindParam(1, $Pbase);
    $Inserir->bindParam(2, $RotasString);
    $Inserir->bindParam(3, $RotasJson);
    $Inserir->bindParam(4, $Matricula);
    $Inserir->bindParam(5, $Marca);
    $Inserir->bindParam(6, $TipoCombustivel);
    $Inserir->bindParam(7, $QuantidadeAbastecida);
    $Inserir->bindParam(8, $PrecoUnitario);
    $Inserir->bindParam(9, $valorPago);
    $Inserir->bindParam(10, $QuantidadeTanque);
    $Inserir->bindParam(11, $KMEstipulada);
    $Inserir->bindParam(12, $QuantidadeEstipulada);
    $Inserir->bindParam(13, $DataAbastecimento);
    $Inserir->bindParam(14, $DiaAbastecimento);
    $Inserir->bindParam(15, $MesAbastecimento);
    $Inserir->bindParam(16, $AnoAbastecimento);
    $Inserir->bindParam(17, $HoraAbastecimento);
    $Inserir->bindParam(18, $IDOrdem);
    $Inserir->bindParam(19, $Semana);
    $Inserir->bindParam(20, $TipoAbastecimento);
    $Inserir->bindParam(21, $Responsavel);
    $Inserir->bindParam(22, $Bombas);
    $Inserir->bindParam(23, $ContagemBomba);

    if ($Inserir->execute())
    {

        return $Resposta = "True";

    }
    else
    {

        return $Resposta = "False";

    }

}

function QuatidaAAbastecer($Estipulada, $Digitada)
{

    $Abastecer;

    if ($Digitada > $Estipulada)
    {

        $Abastecer = $Estipulada;

    }
    else
    {

        $Abastecer = $Digitada;
    }

    return $Abastecer;

}

function PedidoVerificacao($Con, $Matricula, $JRotas, $ArrLength, $Data, $ID_Requisicao)
{

    $Inserir = $Con->prepare("INSERT INTO VerificacaoTanque(Matricula, JRotas, TamanhoArray, Data, ID_Requisicao) VALUES (?,?,?,?,?)");

    $Inserir->bindParam(1, $Matricula);
    $Inserir->bindParam(2, $JRotas);
    $Inserir->bindParam(3, $ArrLength);
    $Inserir->bindParam(4, $Data);
    $Inserir->bindParam(5, $ID_Requisicao);
    $Inserir->execute();

}

function TimeWorkFlow($DataHoraInserido, $DataHoraNow)
{

    $start_date = new DateTime($DataHoraInserido);
    $since_start = $start_date->diff(new DateTime($DataHoraNow));
    $Result;

    if ($since_start->y != 0 && $since_start->days != 0 && $since_start->m != 0)
    {

        $Result = $since_start->y . " Ano(s) " . $since_start->m . " Mes(es) e " . $since_start->d . " Dia(s) ";

    }
    elseif ($since_start->y == 0 && $since_start->days != 0 && $since_start->m != 0)
    {

        $Result = $since_start->m . " Mes(es) e " . $since_start->d . " Dia(s) ";

    }
    elseif ($since_start->y == 0 && $since_start->days != 0 && $since_start->m == 0)
    {

        $Result = $since_start->d . " Dia(s) ";

    }
    elseif ($since_start->days == 0)
    {

        $Result = $since_start->h . " Horas e " . $since_start->i . " Minutos ";
    }

    return $Result;

}

function Upload($IMG_NAME, $NREQUISICAO, $CON)
{

    $Inserir = $CON->prepare("INSERT INTO REQUISICOES2(Cotacao) VALUES (?)");
    $Inserir->bindParam(1, $IMG_NAME);

    if ($Inserir->execute())
    {

        $Diretirio = '../UploadCotacoes/' . $NREQUISICAO . '/';
        mkdir($NREQUISICAO, 0755);
        move_uploaded_file($_FILES[Cotacao][tmp_name], $Diretirio . $Nome_IMG);
    }
    else
    {

    }

}

function DiasDaSemana($Con, $CodigoPedido, $Str)
{

    $Status;
    $Buscar = $Con->prepare("SELECT * FROM PedidoMovimentacao WHERE Codigo_Pedido=?");

    $Buscar->bindParam(1, $CodigoPedido);
    $Buscar->execute();

    if ($Buscar->rowCount() >= 1)
    {

        $CarregarDados = $Buscar->fetch(PDO::FETCH_OBJ);

        $Viaturas = json_decode($CarregarDados->Produtos_Carros, true);

        $Palavra = "";

        foreach ($Viaturas as $key)
        {

            $Palavra .= $key["product_name"] . "<br>";

        }

        if (strpos($Palavra, $Str) !== false)
        {
            $Status = 1;
        }
        else
        {
            $Status = 0;
        }

        return $Status;
    }
    else
    {
        $Status = 3;
        return $Status;
    }

}

function getStartAndEndDate($week, $year)
{
    $dateTime = new DateTime();
    $dateTime->setISODate($year, $week);
    $result['0'] = $dateTime->format('d-M-Y');

    $dateTime->modify('+1 days');
    $result['1'] = $dateTime->format('d-M-Y');

    $dateTime->modify('+1 days');
    $result['2'] = $dateTime->format('d-M-Y');

    $dateTime->modify('+1 days');
    $result['3'] = $dateTime->format('d-M-Y');

    $dateTime->modify('+1 days');
    $result['4'] = $dateTime->format('d-M-Y');

    $dateTime->modify('+1 days');
    $result['5'] = $dateTime->format('d-M-Y');

    $dateTime->modify('+1 days');
    $result['6'] = $dateTime->format('d-M-Y');
    return $result;
}

function encrypt_decrypt($action, $string)
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6';
    $secret_iv = 'PFUXELACORPORATETANZANITEGROUP202012345678900987654321';
    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv) , 0, 16);
    if ($action == 'encrypt')
    {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    }
    else if ($action == 'decrypt')
    {
        $output = openssl_decrypt(base64_decode($string) , $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function EviarEmail($De, $Para, $Assunto, $Titulo, $Mensagem, $LinkDocumento, $Ccs)
{

    $to = $Para;
    $subject = $Assunto;

    $message = "
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<meta name='viewport' content='width=device-width, initial-scale=1.0'/>
	<title>[SUBJECT]</title>
	<style type='text/css'>

@media screen and (max-width: 600px) {
    table[class='container'] {
        width: 95% !important;
    }
}

	#outlook a {padding:0;}
		body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
		.ExternalClass {width:100%;}
		.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
		#backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
		img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
		a img {border:none;}
		.image_fix {display:block;}
		p {margin: 1em 0;}
		h1, h2, h3, h4, h5, h6 {color: black !important;}

		h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: blue !important;}

		h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {
			color: red !important; 
		 }

		h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
			color: purple !important; 
		}

		table td {border-collapse: collapse;}

		table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }

		a {color: #000;}

		@media only screen and (max-device-width: 480px) {

			a[href^='tel'], a[href^='sms'] {
						text-decoration: none;
						color: black; /* or whatever your want */
						pointer-events: none;
						cursor: default;
					}

			.mobile_link a[href^=tel'], .mobile_link a[href^='sms'] {
						text-decoration: default;
						color: orange !important; /* or whatever your want */
						pointer-events: auto;
						cursor: default;
					}
		}


		@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
			a[href^='tel'], a[href^='sms'] {
						text-decoration: none;
						color: blue; /* or whatever your want */
						pointer-events: none;
						cursor: default;
					}

			.mobile_link a[href^='tel'], .mobile_link a[href^='sms'] {
						text-decoration: default;
						color: orange !important;
						pointer-events: auto;
						cursor: default;
					}
		}

		@media only screen and (-webkit-min-device-pixel-ratio: 2) {
			/* Put your iPhone 4g styles in here */
		}

		@media only screen and (-webkit-device-pixel-ratio:.75){
			/* Put CSS for low density (ldpi) Android layouts in here */
		}
		@media only screen and (-webkit-device-pixel-ratio:1){
			/* Put CSS for medium density (mdpi) Android layouts in here */
		}
		@media only screen and (-webkit-device-pixel-ratio:1.5){
			/* Put CSS for high density (hdpi) Android layouts in here */
		}
		/* end Android targeting */
		h2{
			color:#181818;
			font-family:Helvetica, Arial, sans-serif;
			font-size:22px;
			line-height: 22px;
			font-weight: normal;
		}
		a.link1{

		}
		a.link2{
			color:#fff;
			text-decoration:none;
			font-family:Helvetica, Arial, sans-serif;
			font-size:16px;
			color:#fff;border-radius:4px;
		}
		p{
			color:#555;
			font-family:Helvetica, Arial, sans-serif;
			font-size:16px;
			line-height:160%;
		}
	</style>

<script type='colorScheme' class='swatch active'>
  {
    'name':'Default',
    'bgBody':'ffffff',
    'link':'fff',
    'color':'555555',
    'bgItem':'ffffff',
    'title':'181818'
  }
</script>

</head>
<body>
	<!-- Wrapper/Container Table: Use a wrapper table to control the width and the background color consistently of your email. Use this approach instead of setting attributes on the body tag. -->
	<table cellpadding='0' width='100%' cellspacing='0' border='0' id='backgroundTable' class='bgBody'>
	<tr>
		<td>
	<table cellpadding='0' width='620' class='container' align='center' cellspacing='0' border='0'>
	<tr>
		<td>
		<!-- Tables are the most common way to format your email consistently. Set your table widths inside cells and in most cases reset cellpadding, cellspacing, and border to zero. Use nested tables as a way to space effectively in your message. -->
		

		<table cellpadding='0' cellspacing='0' border='0' align='center' width='600' class='container'>
			<tr>
				<td class='movableContentContainer bgItem'>

					<div class='movableContent'>
						<table cellpadding='0' cellspacing='0' border='0' align='center' width='600' class='container'>
							<tr height='40'>
								<td width='200'>&nbsp;</td>
								<td width='200'>&nbsp;</td>
								<td width='200'>&nbsp;</td>
							</tr>
							<tr>
								<td width='200' valign='top'>&nbsp;</td>
								<td width='200' valign='top' align='center'>
									<div class='contentEditableContainer contentImageEditable'>
					                	<div class='contentEditable' align='center' >
					                  		<img src='http://relatorios.executivetransports.co.mz/files/assets/images/logo.png' alt='Logo'  data-default='placeholder' />
					                	</div>
					              	</div>
								</td>
								<td width='200' valign='top'>&nbsp;</td>
							</tr>
							<tr height='25'>
								<td width='200'>&nbsp;</td>
								<td width='200'>&nbsp;</td>
								<td width='200'>&nbsp;</td>
							</tr>
						</table>
					</div>

					<div class='movableContent'>
						<table cellpadding='0' cellspacing='0' border='0' align='center' width='600' class='container'>
							<tr>
								<td width='100%' colspan='3' align='center' style='padding-bottom:10px;padding-top:25px;'>
									<div class='contentEditableContainer contentTextEditable'>
					                	<div class='contentEditable' align='center' >
					                  		<h2 >$Titulo</h2>
					                	</div>
					              	</div>
								</td>
							</tr>
							<tr>
								<td width='100'>&nbsp;</td>
								<td width='400' align='center'>
									<div class='contentEditableContainer contentTextEditable'>
					                	<div class='contentEditable' align='left' >
					                  	$Mensagem
										<b>Nota:</b> Não respondam a este Email,Para Baixar o documento Clique <a href='$LinkDocumento'>AQUI</a>. Caso tenha   alguma dúvida contactem o webmaster@corporategifts.co.mz
					                	</div>
					                	
					                	
					                	</div>
					              	</div>
								</td>
								<td width='100'>&nbsp;</td>
							</tr>
						</table>
						<table cellpadding='0' cellspacing='0' border='0' align='center' width='600' class='container'>
							<tr>
								<td width='200'>&nbsp;</td>
								<td width='200' align='center' style='padding-top:25px;'>
							
						</table>
					</div>


					<div class='movableContent'>
						<table cellpadding='0' cellspacing='0' border='0' align='center' width='600' class='container'>
							<tr>
								<td width='100%' colspan='2' style='padding-top:65px;'>
									<hr style='height:1px;border:none;color:#333;background-color:#ddd;' />
								</td>
							</tr>
							<tr>
								<td width='60%' height='70' valign='middle' style='padding-bottom:20px;'>
									<div class='contentEditableContainer contentTextEditable'>
					                	<div class='contentEditable' align='left' >
					                  		<span style='font-size:13px;color:#181818;font-family:Helvetica, Arial, sans-serif;line-height:200%;'></span>
											<br/>
											<span style='font-size:11px;color:#555;font-family:Helvetica, Arial, sans-serif;line-height:200%;'>Pfixela Exhibitions, Tel:+258 21 32 7725, Av. Paulo Samuel Kankhomba, Maputo - Moçambique</span>
											<br/>
											<span style='font-size:13px;color:#181818;font-family:Helvetica, Arial, sans-serif;line-height:200%;'>
											<a target='_blank' href='' style='text-decoration:none;color:#555'></a>
											</span>
											<br/>
											<span style='font-size:13px;color:#181818;font-family:Helvetica, Arial, sans-serif;line-height:200%;'>
											<a target='_blank' href='[UNSUBSCRIBE]' style='text-decoration:none;color:#555'></a></span>
					                	</div>
					              	</div>
								</td>
								<td width='40%' height='70' align='right' valign='top' align='right' style='padding-bottom:20px;'>
									<table width='100%' border='0' cellspacing='0' cellpadding='0' align='right'>
										<tr>
											<td width='57%'></td>
											<td valign='top' width='34'>
												<div class='contentEditableContainer contentFacebookEditable' style='display:inline;'>
							                        <div class='contentEditable' >
							                           
							                        </div>
							                    </div>
											</td>
											<td valign='top' width='34'>
												<div class='contentEditableContainer contentTwitterEditable' style='display:inline;'>
							                      <div class='contentEditable' >
							                        
							                      </div>
							                    </div>
											</td>
											<td valign='top' width='34'>
												<div class='contentEditableContainer contentImageEditable' style='display:inline;'>
							                      <div class='contentEditable' >
							                        <a target='_blank' href='#' data-default='placeholder'  style='text-decoration:none;'>
														
													</a>
							                      </div>
							                    </div>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</div>


				</td>
			</tr>
		</table>

		
		

	</td></tr></table>
	
		</td>
	</tr>
	</table>


</body>
</html>
";

    // Always set content-type when sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    // More headers
    $headers .= 'From: ' . $De . "\r\n";
    $headers .= 'Cc: ' . $Ccs . "\r\n";

    mail($to, $subject, $message, $headers);

}

function ViaturaValida($Con, $Matricula)
{

    $Buscar = $Con->prepare("SELECT * FROM viatura WHERE Matricula=?");
    $Buscar->bindParam(1, $Matricula);

    $Buscar->execute();

    return $Buscar->rowCount();
}

//YYYY-m-d
function DiaDaSemena($Data)
{

    //Convert the date string into a unix timestamp.
    $unixTimestamp = strtotime($Data);

    //Get the day of the week using PHP's date function.
    $dayOfWeek = date("l", $unixTimestamp);

    return $dayOfWeek;
}
//YYYY-m-d
function NumeroDaSemana($DCheK)
{

    $DescobrirDiaDaSemana = new DateTime($DCheK);
    $weekNumber = $DescobrirDiaDaSemana->format("W");

    return $weekNumber;
}

function PegarNumeroAutorizacao($Con)
{

    $BuscarNumero = $Con->prepare("SELECT * FROM request");
    $BuscarNumero->execute();

    $CarregarNumero = $BuscarNumero->fetch(PDO::FETCH_OBJ);

    $Numero = str_pad($CarregarNumero->Pedido + 0, 5, 0, STR_PAD_LEFT);

    return $Numero;

}

function ActualizarNumeroPedido($Con)
{

    $BuscarNumero = $Con->prepare("SELECT * FROM request");
    $BuscarNumero->execute();

    $CarregarNumero = $BuscarNumero->fetch(PDO::FETCH_OBJ);

    $NovoNumero = $CarregarNumero->Pedido + 1;

    $UpdateNumero = $Con->prepare("UPDATE request SET Pedido=?");
    $UpdateNumero->bindParam(1, $NovoNumero);
    $UpdateNumero->execute();

}

function RotaUsadaParaCheckList($Conexao, $Rotas, $Dia, $Mes, $Ano)
{
    $RowC;

    foreach ($Rotas as $Roots)
    {

        $BuscarRow = $Conexao->prepare("SELECT * FROM SaidaViaturas WHERE Rotas LIKE '%$Roots%' and Dia=? and Mes=? and Ano=?");
        $BuscarRow->bindParam(1, $Dia);
        $BuscarRow->bindParam(2, $Mes);
        $BuscarRow->bindParam(3, $Ano);
        $BuscarRow->execute();

        $RowC += $BuscarRow->rowCount();

    }

    return $RowC;

}

function verificarSeEstaFora($Conn, $Matricula)
{
    $HE = "";
    $Buscar = $Conn->prepare("SELECT * FROM SaidaViaturas WHERE HEntrada=? and Matricula=?");
    $Buscar->bindParam(1, $HE);
    $Buscar->bindParam(2, $Matricula);

    $Buscar->execute();

    $linhas = $Buscar->rowCount();

    return $linhas;

}

function SubTimeOneHour($Hora)
{
    $date = new DateTime($Hora);
    $date->sub(new DateInterval('PT30M'));
    $Time = $date->format('H:i:s') . "\n";

    return $Time;

}

function ValidarIDSaida($Conn, $IdEncrypted)
{

    $NovoID = encrypt_decrypt("decrypt", $IdEncrypted);

    $BuscarSaida = $Conn->prepare("SELECT * FROM SaidaViaturas WHERE ID_SaidaViaturas=?");
    $BuscarSaida->bindParam(1, $NovoID);
    $BuscarSaida->execute();

    return $BuscarSaida->rowCount();

}

function BuscarNumeroDoPedidoNoChekList($Conn, $Matricula)
{

    $Buscar = $Conn->prepare("SELECT * FROM CheckL WHERE Matricula=? ORDER BY ID_CheckList DESC LIMIT 1");
    $Buscar->bindParam(1, $Matricula);
    $Buscar->execute();

    $Carregar = $Buscar->fetch(PDO::FETCH_OBJ);

    return $Carregar->Cod_Referencia;

}

function BuscarMatriculaPeloID($Con, $ID)
{

    $Buscar = $Con->prepare("SELECT * FROM SaidaViaturas WHERE ID_SaidaViaturas=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    $Carregar = $Buscar->fetch(PDO::FETCH_OBJ);

    return $Carregar->Matricula;

}

function PegarCartaMotorista($Con, $ID)
{

    $Buscar = $Con->prepare("SELECT * FROM Colaboradores WHERE ID_Colaborador=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    $C = $Buscar->fetch(PDO::FETCH_OBJ);

    $Carta = $C->Carta_de_Conducao;

    return $Carta;

}

function PegarNomeMotorista($Con, $ID)
{

    $Buscar = $Con->prepare("SELECT * FROM Colaboradores WHERE ID_Colaborador=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    $C = $Buscar->fetch(PDO::FETCH_OBJ);

    $Carta = $C->Nome_Motorista . " " . $C->SobreNome_Motorista;

    return $Carta;

}

function sendMail($to, $from, $fromName, $subject, $file, $htmlContent)
{

    // Header for sender info
    $headers = "From: $fromName" . " <" . $from . ">";

    // Boundary
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

    // Headers for attachment
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

    // Multipart boundary
    $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $htmlContent . "\n\n";

    // Preparing attachment
    if (!empty($file) > 0)
    {
        if (is_file($file))
        {
            $message .= "--{$mime_boundary}\n";
            $fp = @fopen($file, "rb");
            $data = @fread($fp, filesize($file));

            @fclose($fp);
            $data = chunk_split(base64_encode($data));
            $message .= "Content-Type: application/octet-stream; name=\"" . basename($file) . "\"\n" . "Content-Description: " . basename($file) . "\n" . "Content-Disposition: attachment;\n" . " filename=\"" . basename($file) . "\"; size=" . filesize($file) . ";\n" . "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
        }
    }
    $message .= "--{$mime_boundary}--";
    $returnpath = "-f" . $from;

    // Send email
    $mail = @mail($to, $subject, $message, $headers, $returnpath);

}

function BuscarKMInicial($Con, $ID)
{

    $Buscar = $Con->prepare("SELECT * FROM SaidaViaturas WHERE ID_SaidaViaturas=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    $C = $Buscar->fetch(PDO::FETCH_OBJ);

    $KMS = $C->KMSaida;

    return $KMS;

}

function BuscarExistencaiaJBCARD($Con, $NR)
{

    $Buscar = $Con->prepare("SELECT * FROM JobCards WHERE JoBCarCode=?");
    $Buscar->bindParam(1, $NR);
    $Buscar->execute();

    if ($Buscar->rowCount() >= 1)
    {

        return 1;
    }
    else
    {

        return;
    }

}

function BuscarExistencaiaJBCARDState($Con, $NR)
{
    $Estado = 10;
    $Buscar = $Con->prepare("SELECT * FROM JobCards WHERE JoBCarCode=? and Estado >=?");
    $Buscar->bindParam(1, $NR);
    $Buscar->bindParam(2, $Estado);
    $Buscar->execute();

    if ($Buscar->rowCount() >= 1)
    {

        return 1;
    }
    else
    {

        return;
    }

}

function BuscarOcorrenciaValida($Con, $NR)
{

    $Buscar = $Con->prepare("SELECT * FROM NovasOcorrencias WHERE CodigoDaOcorrencia=?");
    $Buscar->bindParam(1, $NR);
    $Buscar->execute();

    if ($Buscar->rowCount() >= 1)
    {

        return 1;
    }
    else
    {

        return;
    }

}

function ResolverOcorrencia($Con, $ID)
{

    $BuscarCodigoOcorencia = $Con->prepare("SELECT * FROM JobCards WHERE ID_JobCard=?");
    $BuscarCodigoOcorencia->bindParam(1, $ID);
    $BuscarCodigoOcorencia->execute();

    $Carregar = $BuscarCodigoOcorencia->fetch(PDO::FETCH_OBJ);
    $Codigo = $Carregar->Codigo_Ocorrencia;

    $Status = 1;
    $Update = $Con->prepare("UPDATE NovasOcorrencias SET Estado=? WHERE CodigoDaOcorrencia=?");
    $Update->bindParam(1, $Status);
    $Update->bindParam(2, $Codigo);
    $Update->execute();

}

function BuscarMatriculaPeloCodigoOcorrencia($Con, $Cod)
{

    $Buscar = $Con->prepare("SELECT * FROM NovasOcorrencias WHERE CodigoDaOcorrencia=?");
    $Buscar->bindParam(1, $Cod);
    $Buscar->execute();

    $CarregarOBJ = $Buscar->fetch(PDO::FETCH_OBJ);

    $Matricula = $CarregarOBJ->Matricula;

    return $Matricula;
}

function BuscarMatriculaPeloJBC($Con, $Cod)
{

    $Buscar = $Con->prepare("SELECT * FROM JobCards WHERE JoBCarCode=?");
    $Buscar->bindParam(1, $Cod);
    $Buscar->execute();

    $Carregar = $Buscar->fetch(PDO::FETCH_OBJ);

    $Matricula = $Carregar->Matricula;

    return $Matricula;

}

function BuscarEspecificacoes($Con, $Matricula)
{

    $Buscar = $Con->prepare("SELECT * FROM viatura WHERE Matricula=?");
    $Buscar->bindParam(1, $Matricula);
    $Buscar->execute();

    $Carregar = $Buscar->fetch(PDO::FETCH_OBJ);

    $Especificaceos = array(
        ['Matricula' => $Carregar->Matricula,
        'Marca' => $Carregar->Marca,
        'Ano_Fabrico' => $Carregar->Ano_Fabrico,
        'Modelo' => $Carregar->Modelo,
        'Numero_Motor' => $Carregar->NMotor,
        'Numero_Chasi' => $Carregar->NChasi,
        'Tipo_Combustivel' => $Carregar->TipodeCombustivel]

    );

    $EspecJson = json_encode($Especificaceos);

    return $EspecJson;

}

function ActualizarSituacaoViatura($Con, $Matricula, $State)
{

    $Update = $Con->prepare("UPDATE viatura SET Locate=? WHERE Matricula=?");
    $Update->bindParam(1, $State);
    $Update->bindParam(2, $Matricula);
    $Update->execute();

}

function PegarPedidoCompra($Con)
{

    $BuscarNumero = $Con->prepare("SELECT * FROM request");
    $BuscarNumero->execute();

    $CarregarNumero = $BuscarNumero->fetch(PDO::FETCH_OBJ);

    $Numero = str_pad($CarregarNumero->PedidoCompra + 0, 5, 0, STR_PAD_LEFT);

    return $Numero;

}

function ActualizarNumeroPedidoCompra($Con)
{

    $BuscarNumero = $Con->prepare("SELECT * FROM request");
    $BuscarNumero->execute();

    $CarregarNumero = $BuscarNumero->fetch(PDO::FETCH_OBJ);

    $NovoNumero = $CarregarNumero->PedidoCompra + 1;

    $UpdateNumero = $Con->prepare("UPDATE request SET PedidoCompra=?");
    $UpdateNumero->bindParam(1, $NovoNumero);
    $UpdateNumero->execute();

}

function Uploadfile($Diretorio, $Ficheiro, $Nome_IMG)
{

    mkdir($Diretorio, 0755);
    move_uploaded_file($Ficheiro, $Diretorio . $Nome_IMG);
}

function BuscarDetalhesDaRotapeloID($Con, $ID)
{

    $Buscar = $Con->prepare("SELECT * FROM roots WHERE id_roots=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    $Carregar = $Buscar->fetch(PDO::FETCH_OBJ);

    $DetalhesRota = array(
        ['Nome' => $Carregar->Nome,
        'KM' => $Carregar->KmEST,
        'HS' => $Carregar->Hora_Predifinida_Saida,
        'HC' => $Carregar->Hora_Predifinida_Chegada,
        'Turno' => $Carregar->Periodo]

    );

    return $DetalhesRota;

}

function VerificarRelatorioPreechidoHoje($Con, $Rota, $Dia, $Mes, $Ano)
{

    $Buscar = $Con->prepare("SELECT * FROM RelatorioDoPiquete WHERE Rota LIKE '%$Rota%' and DiaDeRegisto=? and MesDeRegisto=? and AnoDeRegisto=?");

    $Buscar->bindParam(1, $Dia);
    $Buscar->bindParam(2, $Mes);
    $Buscar->bindParam(3, $Ano);
    $Buscar->execute();
    $Rows = $Buscar->rowCount();

    return $Rows;

}

function BuscarReferencia($Con, $Departamento)
{

    $Buscar = $Con->prepare("SELECT * FROM request");
    $Buscar->execute();

    $Carregar = $Buscar->fetch(PDO::FETCH_OBJ);

    if ($Departamento == 1)
    {

        $Referencia = "PFES" . $Carregar->InventarioRefPFES;
        $Numero = $Carregar->InventarioRefPFES + 1;
        $Update = $Con->prepare("UPDATE request SET InventarioRefPFES=?");
        $Update->bindParam(1, $Numero);
        $Update->execute();

    }
    elseif ($Departamento == 2)
    {

        $Referencia = "PFPA" . $Carregar->InventarioRefPFPA;
        $Numero = $Carregar->InventarioRefPFPA + 1;
        $Update = $Con->prepare("UPDATE request SET InventarioRefPFPA=?");
        $Update->bindParam(1, $Numero);
        $Update->execute();
    }
    else
    {
        return;
    }

    return $Referencia;
}

function BuscarNumeroDaRequisicaoPeloID($Con, $ID)
{
    $Numero;
    $Buscar = $Con->prepare('SELECT * FROM REQUISICOES2 WHERE ID_REQUISICOES2=?');
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Numero = $Carregar->Numero_Requisicao;
    }

    return $Numero;

}



function BuscarNumeroDaRequisicaoPeloIDDoPedido($Con, $ID)
{
    $Numero;
    $Buscar = $Con->prepare('SELECT * FROM REQUISICOES WHERE ID_REQUISICOES=?');
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Numero = $Carregar->Numero_Requisicao;
    }

    return $Numero;

}

function BuscaValorTotalDaRequisicao($Con, $ID)
{

    $valorTotal;
    $valor;
    $BuscarDetalhes = $Con->prepare("SELECT * FROM REQUISICOES2 WHERE ID_REQUISICOES2=?");
    $BuscarDetalhes->bindParam(1, $ID);
    $BuscarDetalhes->execute();

    while ($Carregar = $BuscarDetalhes->fetch(PDO::FETCH_OBJ))
    {

        $Produtos = json_decode($Carregar->Produtos, true);

        foreach ($Produtos as $key)
        {

            $valor += $key["Preco"] * $key["Quantidade"];
        }

        if ($Carregar->IVA != "N")
        {
            $valorTotal = $valor + ($valor * 0.17);
        }
        else
        {
            $valorTotal = $valor;
        }

    }

    return $valorTotal;

}

function verificarExistenciadePagamento($Con, $NREQ)
{

    $ValorPreviamentePago;

    $Buscar = $Con->prepare("SELECT * FROM PagamentoDeRequisicoes WHERE NumeroRequisicao=?");

    $Buscar->bindParam(1, $NREQ);
    $Buscar->execute();

    if ($Buscar->rowCount() == 0)
    {

        $ValorPreviamentePago = 0;

    }
    else
    {

        while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
        {

            $ValorPreviamentePago += $Carregar->ValorPago;

        }

    }

    return $ValorPreviamentePago;
}

function BuscarMoeda($Con, $REQ)
{

    $Moeda;

    $Buscar = $Con->prepare("SELECT * FROM REQUISICOES2 WHERE Numero_Requisicao=?");
    $Buscar->bindParam(1, $REQ);
    $Buscar->execute();

    while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Moeda = $Carregar->Moeda;

    }
    return $Moeda;
}

function UpdateEstadoPosPagamento($Con, $REQ, $StateNumber)
{

    $Actualizar = $Con->prepare("UPDATE REQUISICOES2 SET Estado=? WHERE Numero_Requisicao=?");

    $Actualizar->bindParam(1, $StateNumber);
    $Actualizar->bindParam(2, $REQ);
    $Actualizar->execute();

}

function BuscarDadosFornecedorPeloID($Con, $ID)
{

    $ArrayData;
    $Buscar = $Con->prepare("SELECT * FROM Fornecedores WHERE ID_Fornecedor=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $ArrayData = array(
            ["Nome" => $Carregar->Nome_Fornecedor,
            "Telefone" => $Carregar->Cotacto_Fornecedor,
            "Email" => $Carregar->Email_Fornecedor,
            "NUIT" => $Carregar->NUIT_Fornecedor,
            "Localizacao" => $Carregar->Localizacao,
            "Pais" => $Carregar->Pais,
            "Cidade" => $Carregar->Cidade,
            "CodigoPostal" => $Carregar->Codigo_Postal,
            "ContaBIM" => $Carregar->NContaBim,
            "ContaBCI" => $Carregar->NContaBCI,
            "ContaStandardBank" => $Carregar->NContaStandardBank]
        );

    }
    $a = json_encode($ArrayData);

    return $a;

}

function BuscaPagamentosFeitos($Con, $REQ)
{

    $Contador = 1;
    $Pagamentos = array();

    $BuscarPagamento = $Con->prepare("SELECT * FROM PagamentoDeRequisicoes WHERE NumeroRequisicao=?");
    $BuscarPagamento->bindParam(1, $REQ);
    $BuscarPagamento->execute();

    if ($BuscarPagamento->rowCount() >= 2)
    {

        while ($Carregar = $BuscarPagamento->fetch(PDO::FETCH_OBJ))
        {

            array_push($Pagamentos, [$Carregar->Data_Pagamento, $Carregar->Forma_Pagamento, $Carregar->DetalhesTransacao, $Carregar->ValorPago, $Carregar->Remaescente_A_Pagar, $Carregar->Moeda, $Carregar->Recibo, $Carregar->Comprovativo, "Parcela N $Contador", $Carregar->Responsavel]);

            $Contador += 1;
        }

    }
    else
    {

        while ($Carregar = $BuscarPagamento->fetch(PDO::FETCH_OBJ))
        {

            array_push($Pagamentos, [$Carregar->Data_Pagamento, $Carregar->Forma_Pagamento, $Carregar->DetalhesTransacao, $Carregar->ValorPago, $Carregar->Remaescente_A_Pagar, $Carregar->Moeda, $Carregar->Recibo, $Carregar->Comprovativo, $Carregar->Responsavel]);

        }

    }

    return $Pagamentos;

}

function BuscarValorRemanescentePorPagar($Con, $REQ)
{

    $Remanescente;
    $Buscar = $Con->prepare("SELECT * FROM PagamentoDeRequisicoes WHERE NumeroRequisicao=?");
    $Buscar->bindParam(1, $REQ);
    $Buscar->execute();

    while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Remanescente += $Carregar->Remaescente_A_Pagar;
    }

    return $Remanescente;
}

function BuscarUtilizadorPeloID($Con, $ID)
{
    $Nome;
    $Busca = $Con->prepare("SELECT * FROM users WHERE id_user=?");
    $Busca->bindParam(1, $ID);
    $Busca->execute();
    while ($C = $Busca->fetch(PDO::FETCH_OBJ))
    {

        $Nome = $C->nome . " " . $C->Snome;

    }

    return $Nome;

}

function BuscarEmailDoUtilizador($Con, $ID)
{
    $Email;
    $Busca = $Con->prepare("SELECT * FROM users WHERE id_user=?");
    $Busca->bindParam(1, $ID);
    $Busca->execute();
    while ($C = $Busca->fetch(PDO::FETCH_OBJ))
    {

        $Email = $C->email;

    }

    return $Email;

}

function BuscarSituacaoDePagamentos($Con, $REQ, $TotalRequisitado)
{
    $Resposta;
    $BuscarPAGAMENTO = $Con->prepare("SELECT * FROM PagamentoDeRequisicoes WHERE NumeroRequisicao=?");
    $BuscarPAGAMENTO->bindParam(1, $REQ);
    $BuscarPAGAMENTO->execute();

    if ($BuscarPAGAMENTO->rowCount() >= 1)
    {

        $TotalPago;
        $BuscarValores = $Con->prepare("SELECT SUM(ValorPago) AS PAYD FROM PagamentoDeRequisicoes WHERE NumeroRequisicao=?");
        $BuscarValores->bindParam(1, $REQ);
        $BuscarValores->execute();

        while ($C = $BuscarValores->fetch(PDO::FETCH_OBJ))
        {

            $TotalPago = $C->PAYD;

        }

        if (($TotalPago - $TotalRequisitado) == 0)
        {

            $Resposta = 1;
        }
        else
        {

            $Resposta = 2;
        }

    }
    else
    {

        $Resposta = 0;

    }

    return $Resposta;

}

function SelectViaturas($Con, $Estado)
{

    $Buscar = $Con->prepare("SELECT * FROM viatura WHERE Active=?");
    $Buscar->bindParam(1, $Estado);
    $Buscar->execute();

    return $Buscar;

}

function BuscarRequisitante($Con, $IDREQ)
{

    $ID;
    $Buscar = $Con->prepare("SELECT * FROM REQUISICOES2 WHERE ID_REQUISICOES2=?");
    $Buscar->bindParam(1, $IDREQ);
    $Buscar->execute();

    while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $ID = $Carregar->ID_User;
    }

    return $ID;

}

function BuscarRequisitantePeloNumeroDaRequisicao($Con, $REQNR)
{

    $ID;
    $Buscar = $Con->prepare("SELECT * FROM REQUISICOES2 WHERE Numero_Requisicao=?");
    $Buscar->bindParam(1, $REQNR);
    $Buscar->execute();

    while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $ID = $Carregar->ID_User;
    }

    return $ID;

}
function multi_attach_mail($to, $subject, $message, $senderEmail, $senderName, $files = array()){ 
   
    $from = $senderName." <".$senderEmail.">";  
    $headers = "From: $from"; 
 
     
    $semi_rand = md5(time());  
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";  
 
    
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";  
 

    $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" . 
    "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";  
 
  
    if(!empty($files)){ 
        for($i=0;$i<count($files);$i++){ 
            if(is_file($files[$i])){ 
                $file_name = basename($files[$i]); 
                $file_size = filesize($files[$i]); 
                 
                $message .= "--{$mime_boundary}\n"; 
                $fp =    @fopen($files[$i], "rb"); 
                $data =  @fread($fp, $file_size); 
                @fclose($fp); 
                $data = chunk_split(base64_encode($data)); 
                $message .= "Content-Type: application/octet-stream; name=\"".$file_name."\"\n" .  
                "Content-Description: ".$file_name."\n" . 
                "Content-Disposition: attachment;\n" . " filename=\"".$file_name."\"; size=".$file_size.";\n" .  
                "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n"; 
            } 
        } 
    } 
     
    $message .= "--{$mime_boundary}--"; 
    $returnpath = "-f" . $senderEmail; 
     
 
    $mail = mail($to, $subject, $message, $headers, $returnpath);  
     
    
    if($mail){ 
        return true; 
    }else{ 
        return false; 
    } 
}

function BuscarNumeroDeRequisicoesPeloEstado($Con, $Estado)
{

    $Buscar = $Con->prepare("SELECT * FROM REQUISICOES2 WHERE Estado=?");
    $Buscar->bindParam(1, $Estado);
    $Buscar->execute();

    return $Buscar->rowCount();

}

function BuscarTotalGastoPorMescombustivel($Con, $Mes, $Ano)
{

    $Valor = 0;
    $Buscar = $Con->prepare("SELECT SUM(Valor_Pago) AS VALOR from Combustiveis WHERE Mes_Abastecimento=? and Ano_Abastecimento=?");
    $Buscar->bindParam(1, $Mes);
    $Buscar->bindParam(2, $Ano);
    $Buscar->execute();

    while ($Correr = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Valor += $Correr->VALOR;

    }

    return $Valor;

}

function BuscarLitrosGastoPorMescombustivel($Con, $Mes, $Ano)
{

    $Litros = 0;
    $Buscar = $Con->prepare("SELECT SUM(Quantidade_Abastecida) AS VALOR from Combustiveis WHERE Mes_Abastecimento=? and Ano_Abastecimento=?");
    $Buscar->bindParam(1, $Mes);
    $Buscar->bindParam(2, $Ano);
    $Buscar->execute();

    while ($Correr = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Litros += $Correr->VALOR;

    }

    return $Litros;

}


function BuscarLitrosGastoPorMescombustivelPorTipo($Con, $Mes, $Ano, $Tipo)
{

    $Litros = 0;
    $Buscar = $Con->prepare("SELECT SUM(Quantidade_Abastecida) AS VALOR from Combustiveis WHERE Mes_Abastecimento=? and Ano_Abastecimento=? and Tipo_Combustivel=?");
    $Buscar->bindParam(1, $Mes);
    $Buscar->bindParam(2, $Ano);
    $Buscar->bindParam(3, $Tipo);
    $Buscar->execute();

    while ($Correr = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Litros += $Correr->VALOR;

    }

    return $Litros;

}

function BuscarOrdensDoMes($Con, $Mes, $Ano)
{

    $Buscar = $Con->prepare("SELECT DISTINCT(ID_Ordem_Abastecimento) from Combustiveis WHERE Mes_Abastecimento=? and Ano_Abastecimento=?");
    $Buscar->bindParam(1, $Mes);
    $Buscar->bindParam(2, $Ano);
    $Buscar->execute();

    return $Buscar->rowCount();

}

function VerificacaoProjectoEBombas($Con, $IDOrdem, $PBase, $Bombas)
{

    $Resposta;

    $Estado = "PENDENTE";
    $BuscarPendencia = $Con->prepare("SELECT * FROM EnviarOrdem WHERE Estado=?");
    $BuscarPendencia->bindParam(1, $Estado);
    $BuscarPendencia->execute();

    if ($BuscarPendencia->rowCount() <= 0)
    {

        $Resposta = 0;
    }
    else
    {

        $Buscar = $Con->prepare("SELECT * FROM Combustiveis WHERE Combustiveis.ID_Ordem_Abastecimento=? and  (Combustiveis.ProjectoBase !=? OR Combustiveis.LocalAbastecimento !=?)");
        $Buscar->bindParam(1, $IDOrdem);
        $Buscar->bindParam(2, $PBase);
        $Buscar->bindParam(3, $Bombas);
        $Buscar->execute();

        $Resposta = $Buscar->rowCount();
    }

    return $Resposta;

}

function BuscaEmailDasBomnasPeloNome($Con, $Nome)
{
    $Bombas;
    $BuscarEmail = $Con->prepare("SELECT * FROM Bombas WHERE Nome_Bombas=?");
    $BuscarEmail->bindParam(1, $Nome);
    $BuscarEmail->execute();

    while ($Carregar = $BuscarEmail->fetch(PDO::FETCH_OBJ))
    {

        $Bombas = $Carregar->Email_Bomba;

    }

    return $Bombas;

}

function BuscarNomeDaBombaPeloIDdaOrdem($Con, $IDOrdem)
{
    $Nome;
    $Buscar = $Con->prepare("SELECT DISTINCT(LocalAbastecimento) AS NOMEBOMBAS FROM Combustiveis WHERE ID_Ordem_Abastecimento=?");
    $Buscar->bindParam(1, $IDOrdem);
    $Buscar->execute();

    while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Nome = $Carregar->NOMEBOMBAS;

    }

    return $Nome;
}

function BuscarIdDaUltimaOrdem($Con, $Matricula)
{

    $IDORDEM = 0;
    $Buscar = $Con->prepare("SELECT ID_Ordem_Abastecimento AS ID_ORDEM FROM Combustiveis WHERE Matricula=? ORDER BY ID_Combustiveis DESC LIMIT 1");
    $Buscar->bindParam(1, $Matricula);
    $Buscar->execute();

    while ($Carregar = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $IDORDEM = $Carregar->ID_ORDEM;
    }

    return $IDORDEM;

}

function BuscarPercentagemTipoCombistivelMensal($Con, $cemporcento, $Mes, $TipoCombustivel)
{

    $valor;

    $Buscar = $Con->prepare("SELECT SUM(Quantidade_Abastecida) AS TOTAL FROM Combustiveis WHERE Tipo_Combustivel=? and Mes_Abastecimento=?");
    $Buscar->bindParam(1, $TipoCombustivel);
    $Buscar->bindParam(2, $Mes);
    $Buscar->execute();

    while ($C = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $valor += $C->TOTAL;
    }

    $percentagem = number_format(($valor * 100) / $cemporcento, 2, ",", ".");

    return $percentagem;

}

function CarregarDadosParaoChart($Con, $TipoCombustivel)
{

    $DataHoje = date('d-m-Y');
    $Result;

    for ($i = 182;$i >= 1;$i--)
    {

        $Dia = date('d', strtotime("-$i days", strtotime($DataHoje)));
        $Mes = date('m', strtotime("-$i days", strtotime($DataHoje)));
        $Ano = date('Y', strtotime("-$i days", strtotime($DataHoje)));

        $Buscar = $Con->prepare("SELECT SUM(Quantidade_Abastecida) AS TOTAL FROM Combustiveis WHERE Dia_Abastecimento=? and Mes_Abastecimento=? and Ano_Abastecimento=? and Tipo_Combustivel=?");

        $Buscar->bindParam(1, $Dia);
        $Buscar->bindParam(2, $Mes);
        $Buscar->bindParam(3, $Ano);
        $Buscar->bindParam(4, $TipoCombustivel);
        $Buscar->execute();

        while ($C = $Buscar->fetch(PDO::FETCH_OBJ))
        {

            if ($i == 0)
            {
                if ($C->TOTAL == "")
                {

                    $Result .= "[handleGetDate($i), 0]";

                }
                else
                {

                    $Result .= "[handleGetDate($i), $C->TOTAL],";

                }

            }elseif($i == 1)
            {
               $Result .= "[handleGetDate($i), $C->TOTAL]"; 
            }
            else
            {

                if ($C->TOTAL == "")
                {

                    $Result .= "[handleGetDate($i), 0],";

                }
                else
                {

                    $Result .= "[handleGetDate($i), $C->TOTAL],";

                }

            }

        }

    }

    return $Result;

}

function CarregarDadosParaoChart2($Con, $TipoCombustivel)
{

    $DataHoje = date('d-m-Y');
    $Result = array();

    for ($i = 182;$i >= 1;$i--)
    {

        $Dia = date('d', strtotime("-$i days", strtotime("$DataHoje")));
        $Mes = date('m', strtotime("-$i days", strtotime("$DataHoje")));
        $Ano = date('Y', strtotime("-$i days", strtotime("$DataHoje")));

        $Buscar = $Con->prepare("SELECT SUM(Quantidade_Abastecida) AS TOTAL FROM Combustiveis WHERE Dia_Abastecimento=? and Mes_Abastecimento=? and Ano_Abastecimento=? and Tipo_Combustivel=?");

        $Buscar->bindParam(1, $Dia);
        $Buscar->bindParam(2, $Mes);
        $Buscar->bindParam(3, $Ano);
        $Buscar->bindParam(4, $TipoCombustivel);
        $Buscar->execute();

        while ($C = $Buscar->fetch(PDO::FETCH_OBJ))
        {

            if ($i == 0)
            {
                if ($C->TOTAL == "")
                {

                    array_push($Result, "[handleGetDate($i), 0]");

                }
                else
                {
                    array_push($Result, "[handleGetDate($i), $C->TOTAL]");

                }

            }
            else
            {

                if ($C->TOTAL == "")
                {

                    array_push($Result, "[handleGetDate($i), 0]");

                }
                else
                {

                    array_push($Result, "[handleGetDate($i), $C->TOTAL]");

                }

            }

        }

    }

    return $Result;

}

function VerificarEstadoDaOrdem($Con, $IDOrdem)
{

    $Estado;
    $Buscar = $Con->prepare("SELECT * FROM EnviarOrdem WHERE ID_Ordem=?");
    $Buscar->bindParam(1, $IDOrdem);
    $Buscar->execute();

    while ($C = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Estado .= $C->Estado;

    }

    return $Estado;
}

function getIsoWeeksInYear($year)
{
    $date = new DateTime;
    $date->setISODate($year, 53);
    return ($date->format("W") === "53" ? 53 : 52);
}

function PreviusWeekNumber($ActualWeak, $Year)
{

    if ($ActualWeak === 1)
    {

        $TransformYearIntolastYear = $Year - 1;

        $date = new DateTime;
        $date->setISODate($TransformYearIntolastYear, 53);
        $Result = ($date->format("W") === "53" ? 53 : 52);

    }
    else
    {
        $R = $ActualWeak - 1;
        if ($R <= 9)
        {
            $Result = "0" . $R;
        }
        else
        {
            $Result = $R;
        }

    }

    return $Result;

}

function PegarNumeroJBC($Con)
{

    $BuscarNumero = $Con->prepare("SELECT * FROM request");
    $BuscarNumero->execute();

    $CarregarNumero = $BuscarNumero->fetch(PDO::FETCH_OBJ);

    $Numero = $CarregarNumero->fichadeobra;

    return $Numero;

}

function ActualizarNumeroJBC($Con)
{

    $BuscarNumero = $Con->prepare("SELECT * FROM request");
    $BuscarNumero->execute();

    $CarregarNumero = $BuscarNumero->fetch(PDO::FETCH_OBJ);

    $NovoNumero = $CarregarNumero->fichadeobra + 1;

    $UpdateNumero = $Con->prepare("UPDATE request SET fichadeobra=?");
    $UpdateNumero->bindParam(1, $NovoNumero);
    $UpdateNumero->execute();

}

function AbrirJobCard($Con, $Matricula, $JcardCode, $OcorenceCode, $DetalhesDaViatura)
{

    $Dia = date('d');
    $Mes = date('m');
    $Ano = date('Y');
    $Data = date('d-M-Y');

    $Insert = $Con->prepare("INSERT INTO JobCards(Matricula, JoBCarCode, CodigoOcorrencia, DetalhesDaViatura, DiaEntrada,MesEntrada,AnoEntrada,DataEntrada) VALUES (?,?,?,?,?,?,?,?)");
    $Insert->bindParam(1, $Matricula);
    $Insert->bindParam(2, $JcardCode);
    $Insert->bindParam(3, $OcorenceCode);
    $Insert->bindParam(4, $DetalhesDaViatura);
    $Insert->bindParam(5, $Dia);
    $Insert->bindParam(6, $Mes);
    $Insert->bindParam(7, $Ano);
    $Insert->bindParam(8, $Data);
    $Insert->execute();

}

function BuscarExistenciadePedidosParaJOBC($Con, $JBCNUMBER)
{

    $Buscar = $Con->prepare("SELECT * FROM PedidoDeCompra WHERE CodigoJobCard=?");
    $Buscar->bindParam(1, $JBCNUMBER);
    $Buscar->execute();

    return $Buscar->rowCount();
}

function BuscarExistenciaDeRetiradaParaJOBC($Con, $JBCNUMBER, $TypeOp)
{

    $Buscar = $Con->prepare("SELECT * FROM stock WHERE Ficha_De_Obra=? and Tipo_Operacao=?");
    $Buscar->bindParam(1, $JBCNUMBER);
    $Buscar->bindParam(2, $TypeOp);
    $Buscar->execute();

    return $Buscar->rowCount();
}

function BuscarExistenciaDeRecursosAdicionaisParaJOBC($Con, $JBCNUMBER)
{

    $Buscar = $Con->prepare("SELECT * FROM GastosFichasDeObra WHERE JobCardNumber=?");
    $Buscar->bindParam(1, $JBCNUMBER);
    $Buscar->execute();

    return $Buscar->rowCount();
}

function ActualizarEstadoPedido($Con, $JBCNUMBER, $NovoEstado)
{

    $Update = $Con->prepare("UPDATE PedidoDeCompra SET Estado=? WHERE CodigoJobCard=?");
    $Update->bindParam(1, $NovoEstado);
    $Update->bindParam(2, $JBCNUMBER);
    $Update->execute();
}

function ActualizarEstadoOcorrencia($Con, $NOcorrencia, $NovoEstado)
{
    $Dia = date('d');
    $Mes = date('m');
    $Ano = date('Y');
    $Data = date('d-M-Y');
    $Update = $Con->prepare("UPDATE NovasOcorrencias SET Estado=?, Dia_Fechamento=?, Mes_Fechamento=?, Ano_Fechamento=?, Data_Fechamento=? WHERE CodigoDaOcorrencia=?");
    $Update->bindParam(1, $NovoEstado);
    $Update->bindParam(2, $Dia);
    $Update->bindParam(3, $Mes);
    $Update->bindParam(4, $Ano);
    $Update->bindParam(5, $Data);
    $Update->bindParam(6, $NOcorrencia);
    $Update->execute();

}

function ActualizarEstadoFichaDeObra($Con, $NFichaDeObra, $NovoEstado)
{
    $Dia = date('d');
    $Mes = date('m');
    $Ano = date('Y');
    $Data = date('d-M-Y');
    $Update = $Con->prepare("UPDATE JobCards SET Estado=?, DiaSaida=?,MesSaida=?,AnoSaida=?,DataSaida=? WHERE JoBCarCode=?");
    $Update->bindParam(1, $NovoEstado);
    $Update->bindParam(2, $Dia);
    $Update->bindParam(3, $Mes);
    $Update->bindParam(4, $Ano);
    $Update->bindParam(5, $Data);
    $Update->bindParam(6, $NFichaDeObra);

    $Update->execute();
}

function BuscarCodigoDaOcorrenciaPeloNumeroDeFichaDeObra($Con, $NfichaDeObra)
{

    $CodigoDarequisicao;
    $Buscar = $Con->prepare("SELECT CodigoOcorrencia FROM JobCards WHERE JoBCarCode=?");
    $Buscar->bindParam(1, $NfichaDeObra);
    $Buscar->execute();

    while ($C = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $CodigoDarequisicao .= $C->CodigoOcorrencia;

    }

    return $CodigoDarequisicao;
}

function BuscarEstadoFichaDeObra($Con, $JBC, $Estado)
{

    $Buscar = $Con->prepare("SELECT * FROM JobCards WHERE JoBCarCode=? and Estado=?");
    $Buscar->bindParam(1, $JBC);
    $Buscar->bindParam(2, $Estado);
    $Buscar->execute();

    return $Buscar->rowCount();
}

function VerificarExistenciaDorelatiorio($Connection, $D, $M, $Y, $Turno)
{

    $Buscar = $Connection->prepare("SELECT * FROM RelatorioDoPiquete WHERE DiaDeRegisto=? and MesDeRegisto=? and AnoDeRegisto=? and Turno=?");

    $Buscar->bindParam(1, $D);
    $Buscar->bindParam(2, $M);
    $Buscar->bindParam(3, $Y);
    $Buscar->bindParam(4, $Turno);
    $Buscar->execute();

    return $Buscar->rowCount();
}

function MediaDeConsumo($Con, $Matricula)
{

    $BuscarDadosViatura = $Con->prepare("SELECT * FROM viatura WHERE MAtricula=?");
    $BuscarDadosViatura->bindParam(1, $Matricula);
    $BuscarDadosViatura->execute();
    $Carregar = $BuscarDadosViatura->fetch(PDO::FETCH_OBJ);

    return $Carregar->LitrosPorQuilometro;

}

function BuscarDadosDoUltimoAbastecimento($Con, $Matricula)
{

    $Buscar = $Con->prepare("SELECT MAX(ID_Ordem_Abastecimento) AS COD FROM Combustiveis WHERE Matricula=? ORDER BY ID_Combustiveis DESC");
    $Buscar->bindParam(1, $Matricula);
    $Buscar->execute();

    $C = $Buscar->fetch(PDO::FETCH_OBJ);

    return $C->COD;
}

function TotalGastoPercurso($Con, $Matricula, $Codigo)
{

    $Buscar = $Con->prepare("SELECT SUM(KMEntrada-KMSaida) AS DIFF from SaidaViaturas WHERE Codigo_Ordem=? and Matricula=?");
    $Buscar->bindParam(1, $Codigo);
    $Buscar->bindParam(2, $Matricula);
    $Buscar->execute();

    $C = $Buscar->fetch(PDO::FETCH_OBJ);

    return $C->DIFF;

}

function QuantidadeAbstecindanaUltimaOrdem($Con, $Matricula, $CodigoDaOrdem)
{

    $Buscar = $Con->prepare("SELECT Quantidade_Abastecida AS QNT from Combustiveis WHERE ID_Ordem_Abastecimento=? and Matricula=?");
    $Buscar->bindParam(1, $CodigoDaOrdem);
    $Buscar->bindParam(2, $Matricula);
    $Buscar->execute();

    $C = $Buscar->fetch(PDO::FETCH_OBJ);

    return $C->QNT;

}

function Tanque($QuantidadeAbastecida, $QuilometragemPercorrida, $MediaDaViatura)
{

    $QuantidadeConsumida = $QuilometragemPercorrida * $MediaDaViatura;
    $remanescente = $QuantidadeAbastecida - $QuantidadeConsumida;

    if ($remanescente < 0)
    {

        $remanescente = 0;

    }
    else
    {
        $remanescente;
    }

    return $remanescente;

}

function BuscarHoraPrevistaDeSaidaPeloIdDaSaida($Con, $ID)
{
    $Hora;
    $Buscar = $Con->prepare("SELECT Hora_Predifinida_Saida AS HP FROM SaidaViaturas WHERE ID_SaidaViaturas=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();

    while ($C = $Buscar->fetch(PDO::FETCH_OBJ))
    {

        $Hora = $C->HP;

    }

    return $Hora;

}

function timeDiff($firstTime, $lastTime)
{
    $firstTime = strtotime($firstTime);
    $lastTime = strtotime($lastTime);
    $timeDiff = $lastTime - $firstTime;
    return $timeDiff;
}

function BuscarNivelDeAlcoolS($Con, $Carta, $M, $Y)
{

    $BuscarBanco = $Con->prepare("SELECT * FROM SaidaViaturas WHERE NivelAlcoolSaida >0 and Mes=? and Ano=? and Cracha_BI=?");

    $BuscarBanco->bindParam(1, $M);
    $BuscarBanco->bindParam(2, $Y);
    $BuscarBanco->bindParam(3, $Carta);

    $BuscarBanco->execute();
    $C = $BuscarBanco->fetch(PDO::FETCH_OBJ);

    return ($C->NivelAlcoolSaida);
}

function BuscarNivelDeAlcoolE($Con, $Carta, $M, $Y)
{

    $BuscarBanco = $Con->prepare("SELECT * FROM SaidaViaturas WHERE NivelAlcoolEntrada >0 and Mes=? and Ano=? and BI_Entrada=?");

    $BuscarBanco->bindParam(1, $M);
    $BuscarBanco->bindParam(2, $Y);
    $BuscarBanco->bindParam(3, $Carta);

    $BuscarBanco->execute();
    $C = $BuscarBanco->fetch(PDO::FETCH_OBJ);

    return ($C->NivelAlcoolEntrada);
}

function ApresentacaoDaCarta($Con, $Carta, $NumeroCarta, $M, $Y)
{

    $BuscarBanco = $Con->prepare("SELECT * FROM SaidaViaturas WHERE TemCarta=? and Mes=? and Ano=? and Cracha_BI=?");

    $BuscarBanco->bindParam(1, $Carta);
    $BuscarBanco->bindParam(2, $M);
    $BuscarBanco->bindParam(3, $Y);
    $BuscarBanco->bindParam(4, $NumeroCarta);

    $BuscarBanco->execute();

    return ($BuscarBanco->rowCount());
}

function NumeroREQUISICAO($Connection)
{
    $buscarNumeroDeRequisicao = $Connection->prepare("SELECT * FROM request");
    $buscarNumeroDeRequisicao->execute();

    $Varrer = $buscarNumeroDeRequisicao->fetch(PDO::FETCH_OBJ);
    $NumeroDaRequisicao = $Varrer->Numero + 1;

    return $NumeroDaRequisicao;

}

function ApresentarArrayProdutos($Connection, $Produto, $Quantidade)
{

    $x = 0;

    foreach ($Produto as $key)
    {

        $Buscar = $Connection->prepare("SELECT * FROM Produtos WHERE ID_Produtos=?");
        $Buscar->bindParam(1, $key);
        $Buscar->execute();

        $C = $Buscar->fetch(PDO::FETCH_OBJ);

        $item = array(
            'ID_Produto' => $Produto[$x],
            'Descricao' => $C->Nome_Produtos . " - " . $C->Rerefencia_Produtos,
            'Quantidade' => $Quantidade[$x]
        );
        $file_data_array[] = $item;

        $x = $x + 1;

    }

    return $file_data_array;

}

function GerarNumeroDeRequisicao($Connection)
{

    $buscarNumeroDeRequisicao = $Connection->prepare("SELECT * FROM request");
    $buscarNumeroDeRequisicao->execute();

    $Varrer = $buscarNumeroDeRequisicao->fetch(PDO::FETCH_OBJ);

    $NumeroDaRequisicao = $Varrer->Numero + 1;

    return $NumeroDaRequisicao;
}


function GerarNumeroDePedidoDeCompra($Connection)
{

    $buscarNumeroDeRequisicao = $Connection->prepare("SELECT * FROM request");
    $buscarNumeroDeRequisicao->execute();

    $Varrer = $buscarNumeroDeRequisicao->fetch(PDO::FETCH_OBJ);

    $NumeroDaRequisicao = $Varrer->PedidoCompra + 1;

    return $NumeroDaRequisicao;
}

function ActualizarNumeroDeRequisicao($Connection, $NumeroDaRequisicao)
{

    $updateNreq = $Connection->prepare("UPDATE request SET Numero=?");
    $updateNreq->bindParam(1, $NumeroDaRequisicao);
    $updateNreq->execute();
}


function ActualizarNumeroDePedido($Connection, $NumeroDePedido)
{
    
    
    $updateNreq = $Connection->prepare("UPDATE request SET PedidoCompra=?");
    $updateNreq->bindParam(1, $NumeroDePedido);
    $updateNreq->execute();
}

function BuscarArrayProdutosdaDB($Con, $NumeroDarequisicao)
{

    $Busca = $Con->prepare("SELECT * FROM REQUISICOES WHERE Numero_Requisicao=?");
    $Busca->bindParam(1, $NumeroDarequisicao);
    $Busca->execute();

    $C = $Busca->fetch(PDO::FETCH_OBJ);

    return ($C->JsonProdutos);

}

function BuscarArrayQunatidadesdaDB($Con, $NumeroDarequisicao)
{

    $Busca = $Con->prepare("SELECT * FROM REQUISICOES WHERE Numero_Requisicao=?");
    $Busca->bindParam(1, $NumeroDarequisicao);
    $Busca->execute();

    $C = $Busca->fetch(PDO::FETCH_OBJ);

    return ($C->JsonQuantidade);

}

function BuscarUtimoCodigoDaOrdemPendente($Con)
{
    $Estado = "PENDENTE";
    $Buscar = $Con->prepare("SELECT * FROM EnviarOrdem WHERE Estado=? ORDER BY ID_Ordem DESC");
    $Buscar->bindParam(1, $Estado);
    $Buscar->execute();

    $C = $Buscar->fetch(PDO::FETCH_OBJ);

    return ($C->ID_Ordem);

}

function VerificarExistenciaDeOrdemPendente($Con, $NumeroDaOrdem)
{
    $Estado = 'PENDENTE';
    $Buscar = $Con->prepare("SELECT * FROM EnviarOrdem WHERE Estado=? and ID_Ordem=?");
    $Buscar->bindParam(1, $Estado);
    $Buscar->bindParam(2, $NumeroDaOrdem);
    $Buscar->execute();

    return ($Buscar->rowCount());

}

function NomeFornecedores($Con, $NREQ)
{

    $Buscar = $Con->prepare("SELECT * FROM REQUISICOES WHERE Numero_Requisicao=?");
    $Buscar->bindParam(1, $NREQ);
    $Buscar->execute();

    $C = $Buscar->fetch(PDO::FETCH_OBJ);

    $Ids = json_decode($C->JsonFornecedores);
    $x = 1;

    foreach ($Ids as $key)
    {

        $BuscarNome = $Con->prepare("SELECT * FROM Fornecedores WHERE ID_Fornecedor=?");
        $BuscarNome->bindParam(1, $key);
        $BuscarNome->execute();
        $C = $BuscarNome->fetch(PDO::FETCH_OBJ);

        $item = array(
            'Fornecedor' . $x => $C->Nome_Fornecedor,
            'IDF' . $x => $C->ID_Fornecedor
        );
        $file_data_array[] = $item;

        $x++;

    }

    return $file_data_array;

}

function BuscarArrayPrecosPorFornecedor($Con, $NumeroDarequisicao, $Fornecedor)
{

    $Busca = $Con->prepare("SELECT * FROM REQUISICOES WHERE Numero_Requisicao=?");
    $Busca->bindParam(1, $NumeroDarequisicao);
    $Busca->execute();

    $C = $Busca->fetch(PDO::FETCH_OBJ);

    return ($C->$Fornecedor);

}

function BuscarIvaDeCadaFornecedor($Con, $NumeroDarequisicao, $Fornecedor)
{

    $Busca = $Con->prepare("SELECT * FROM REQUISICOES WHERE Numero_Requisicao=?");
    $Busca->bindParam(1, $NumeroDarequisicao);
    $Busca->execute();

    $C = $Busca->fetch(PDO::FETCH_OBJ);

    $ArrayI = $C->$Fornecedor;

    return ($ArrayI);

}

function BuscarEntradasDeProduto($Con, $Produto)
{
    $Estrada = "Entrada";
    $BuscarQuantidadeInserida = $Con->prepare("SELECT SUM(Quantidade) AS QuantityIn FROM stock WHERE Produto=? and Tipo_Operacao=?");
    $BuscarQuantidadeInserida->bindParam(1, $Produto);
    $BuscarQuantidadeInserida->bindParam(2, $Estrada);
    $BuscarQuantidadeInserida->execute();
    $C = $BuscarQuantidadeInserida->fetch(PDO::FETCH_OBJ);
    $QuantidadeIn = $C->QuantityIn;

    $Saida = "Saida";
    $BuscarQuantidadeRetirada = $Con->prepare("SELECT SUM(Quantidade) AS QuantityOut FROM stock WHERE Produto=? and Tipo_Operacao=?");
    $BuscarQuantidadeRetirada->bindParam(1, $Produto);
    $BuscarQuantidadeRetirada->bindParam(2, $Saida);
    $BuscarQuantidadeRetirada->execute();
    $C = $BuscarQuantidadeRetirada->fetch(PDO::FETCH_OBJ);
    $QuantidadeOut = $C->QuantityOut;

    $QuantidadeDisponivel = ($QuantidadeIn - $QuantidadeOut);

    $Result = json_encode(array(
        "QuantidadeIn" => $QuantidadeIn,
        "QuantidadeOut" => $QuantidadeOut,
        "Disponivel" => $QuantidadeDisponivel
    ));

    return $Result;

}


function BuscarEscolas($Con){
    
    $Buscar = $Con->prepare("SELECT * FROM Escolas");
    $Buscar->execute();
    
    return $Buscar;
    
    
}

function BuscarNomeProdutoPeloID($Con, $ID){
    
    $Buscar = $Con->prepare("SELECT * FROM Produtos WHERE ID_Produtos=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();
    
    $C = $Buscar->fetch(PDO::FETCH_OBJ);
    
    $Nome = $C->Nome_Produtos." ".$C->Rerefencia_Produtos;
    
    return $Nome;
}


function BuscarNomeServicoPeloID($Con, $ID){
    
    $Buscar = $Con->prepare("SELECT * FROM Servicos WHERE ID_Servicos=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();
    
    $C = $Buscar->fetch(PDO::FETCH_OBJ);
    
    $Nome = $C->Nome_Servicos." ".$C->Referencia_Servicos;
    
    return $Nome;
}

function BuscarRequisicoesPorSemana($Con, $Semana){
    
    $Buscar = $Con->prepare("SELECT * FROM PagamentoDeRequisicoes WHERE Semana=?");
    $Buscar->bindParam(1, $Semana);
    $Buscar->execute();
    
    return $Buscar;
    
    
}


function BuscarDadosDoPedido($Con, $NREQ){
    
    $Buscar = $Con->prepare("SELECT * FROM REQUISICOES WHERE Numero_Requisicao=?");
    $Buscar->bindParam(1, $NREQ);
    $Buscar->execute();
    
    return $Buscar;
    
}


function BuscarProdutos($Con){
    
    $Buscar = $Con->prepare("SELECT * FROM Produtos");
    $Buscar->execute();
    
    return $Buscar;
    
    
}


function BuscarServicos($Con){
    
    $Buscar = $Con->prepare("SELECT * FROM Servicos");
    $Buscar->execute();
    
    return $Buscar;
    
    
}

function BuscarNomeDepartamentoPorUser($Con, $UserID){
    
    $Buscar = $Con->prepare("SELECT * FROM Departamento WHERE ID_Departamento=?");
    $Buscar->bindParam(1, $UserID);
    $Buscar->execute();
    
    $Array = $Buscar->fetch(PDO::FETCH_OBJ);
    
    return $Array->Nome_Departamento;
    
    
    
}

function MesAnterior($MesActual)
{
    
    if($MesActual == 1){
        $MesAImprimir = 12;
    }else{
        $MesAImprimir = $MesActual-1;
        
        
    }
    
    
    
    return str_pad($MesAImprimir, 2, "0", STR_PAD_LEFT);
    
}


function GetNomeMes($NumeroMes){
    $Nome;
    
    if($NumeroMes == "01"){
        $Nome = "Janeiro";
        
    }elseif($NumeroMes == "02"){
        $Nome = "Fevereiro";
        
    }elseif($NumeroMes == "03"){
        $Nome = "Março";
        
    }elseif($NumeroMes == "04"){
        $Nome = "Abril";
        
    }elseif($NumeroMes == "05"){
        $Nome = "Maio";
    }elseif($NumeroMes == "06"){
        $Nome = "Junho";
        
    }elseif($NumeroMes == "07"){
        $Nome = "Julho";
        
    }elseif($NumeroMes == "08"){
        $Nome = "Agosto";
        
    }elseif($NumeroMes == "09"){
        $Nome = "Setembro";
        
    }elseif($NumeroMes == "10"){
        $Nome = "Outubro";
        
    }elseif($NumeroMes == "11"){
        $Nome = "Novembro";
        
    }elseif($NumeroMes == "12"){
        $Nome = "Dezembro";
        
    }else{
        
    }
    
    return $Nome;
}

       function ApresentarArrayServicos($Connection, $Servico, $Quantidade)
    {
    
        $x = 0;
    
        foreach ($Servico as $key)
        {
    
            $Buscar = $Connection->prepare("SELECT * FROM Servicos WHERE ID_Servicos=?");
            $Buscar->bindParam(1, $key);
            $Buscar->execute();
    
            $C = $Buscar->fetch(PDO::FETCH_OBJ);
    
            $item = array(
                'ID_Produto' => $Servico[$x],
                'Descricao' => $C->Nome_Servicos . " - " . $C->Referencia_Servicos,
                'Quantidade' => $Quantidade[$x]
            );
            $file_data_array[] = $item;
    
            $x = $x + 1;
    
        }
    
        return $file_data_array;
    
    }
    
    function BuscarTipoPorRequisicao($Con, $ID){
        
        $Buscar = $Con->prepare("SELECT Tipo_Requisicao as TIPO FROM REQUISICOES WHERE ID_REQUISICOES=?");
        $Buscar->bindParam(1, $ID);
        $Buscar->execute();
        $C = $Buscar->fetch(PDO::FETCH_OBJ);
        
        return $C->TIPO;
        
    }
    
    
    function BuscarTipoPorRequisicaoPorNREQ($Con, $ID){
        
        $Buscar = $Con->prepare("SELECT Tipo_Requisicao as TIPO FROM REQUISICOES WHERE Numero_Requisicao=?");
        $Buscar->bindParam(1, $ID);
        $Buscar->execute();
        $C = $Buscar->fetch(PDO::FETCH_OBJ);
        
        return ($C->TIPO);
        
    }

 function AdicionarProtutosAoStock($Con, $NRequisicao, $Operador)
      {
      $Response;
      $Data = date('d-M-Y');
      $Dia = date('d');
      $Mes = date('m');
      $Ano = date('Y');
      
      $Buscar = $Con->prepare("SELECT * FROM REQUISICOES2 WHERE Numero_Requisicao=?");
      $Buscar->bindParam(1, $NRequisicao);
      $Buscar->execute();
      $C = $Buscar->fetch(PDO::FETCH_OBJ);
      
      if($C->FichaDeObra !=""){
          
          $NF =$C->FichaDeObra;
          
      }else{
      
      $NF="Não Proveniente de Ficha de Obra";
          
          
      }
      
      
      
          $x = 0;
          
    if($C->Tipo_Requisicao == "Produtos"){
          
    $Produtos = json_decode($C->OBJIDProdutos);
    $Quantidades = json_decode($C->OBJIDQuatidades);
    $TypeOps = "Entrada";
    $Destino = "Armazem";
    
    
    $VarIN ="Entrada";
    $VarOut = "Saida";
    $Entradas = $Con->prepare("SELECT SUM(Quantidade) as QNTIN FROM stock WHERE Produto=? and Tipo_Operacao=?");
    $Entradas->bindParam(1, $Produtos[$x]);
    $Entradas->bindParam(2, $VarIN);
    $Entradas->execute();
    $Entry = $Entradas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Entrada = $Entry->QNTIN;
    
    
    $Saidas = $Con->prepare("SELECT SUM(Quantidade) as QNTOUT FROM stock WHERE Produto=? and Tipo_Operacao=?");
    $Saidas->bindParam(1, $Produtos[$x]);
    $Saidas->bindParam(2, $VarOut);
    $Saidas->execute();
    $Out = $Saidas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Saida = $Out->QNTOUT;
    
    $QuabtidadeExistente = $Quantidade_de_Entrada-$Quantidade_de_Saida;
    
    $Remanescente = ($QuabtidadeExistente + $Quantidades[$x]);

    foreach ($Produtos as $key)
    {

        $InserirProduto = $Con->prepare("INSERT INTO stock(Produto, Quantidade, Data_Operacao, Tipo_Operacao, Numero_Requisicao, Dia, Mes, Ano, Destino, Operador,Ficha_De_Obra,Remanescente) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        
        $InserirProduto->bindParam(1, $Produtos[$x]);
        $InserirProduto->bindParam(2, $Quantidades[$x]);
        $InserirProduto->bindParam(3, $Data);
        $InserirProduto->bindParam(4, $TypeOps);
        $InserirProduto->bindParam(5, $NRequisicao);
        $InserirProduto->bindParam(6, $Dia);
        $InserirProduto->bindParam(7, $Mes);
        $InserirProduto->bindParam(8, $Ano);
        $InserirProduto->bindParam(9, $Destino);
        $InserirProduto->bindParam(10, $Operador);
        $InserirProduto->bindParam(11, $NF);
        $InserirProduto->bindParam(12, $Remanescente);
        if($InserirProduto->execute()){
            $Response = "Executado";
        }else{
            $Response = "Error".$NF;
        }

       
        $x = $x + 1;

    }
      
    }else{
        $Response = "Tou no Else Mano";
        
    } 
      
      return $Response;
      
        
        
      }
  
  
    
    function RemoverProtutosAoStock($Con, $NRequisicao, $Operador){
    
    
    
      $Response;
      $Data = date('d-M-Y');
      $Dia = date('d');
      $Mes = date('m');
      $Ano = date('Y');
      
      $Buscar = $Con->prepare("SELECT * FROM REQUISICOES2 WHERE Numero_Requisicao=?");
      $Buscar->bindParam(1, $NRequisicao);
      $Buscar->execute();
      $C = $Buscar->fetch(PDO::FETCH_OBJ);
      
      if($C->FichaDeObra !=""){
          
          $NF =$C->FichaDeObra;
          
          
          if($C->Tipo_Requisicao == "Produtos"){
         $x = 0;
          
    $Produtos = json_decode($C->OBJIDProdutos);
    $Quantidades = json_decode($C->OBJIDQuatidades);
    $TypeOps = "Saida";
    $Des = $Con->prepare("SELECT Matricula FROM JobCards WHERE JoBCarCode=?");
    $Des->bindParam(1, $NF);
    $Des->execute();
    $Destino = $Des->fetch(PDO::FETCH_OBJ);
    
    $VarIN ="Entrada";
    $VarOut = "Saida";
    $Entradas = $Con->prepare("SELECT SUM(Quantidade) as QNTIN FROM stock WHERE Produto=? and Tipo_Operacao=?;");
    $Entradas->bindParam(1, $Produtos[$x]);
    $Entradas->bindParam(2, $VarIN);
    $Entradas->execute();
    $Entry = $Entradas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Entrada = $Entry->QNTIN;
    
    
    $Saidas = $Con->prepare("SELECT SUM(Quantidade) as QNTOUT FROM stock WHERE Produto=? and Tipo_Operacao=?;");
    $Saidas->bindParam(1, $Produtos[$x]);
    $Saidas->bindParam(2, $VarOut);
    $Saidas->execute();
    $Out = $Saidas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Saida = $Out->QNTOUT;
    
    $QuabtidadeExistente = $Quantidade_de_Entrada-$Quantidade_de_Saida;
    
    $Remanescente = ($QuabtidadeExistente - $Quantidades[$x]);
    
    

    foreach ($Produtos as $key)
    {

        $InserirProduto = $Con->prepare("INSERT INTO stock(Produto, Quantidade, Data_Operacao, Tipo_Operacao, Dia, Mes, Ano, Destino, Operador,Ficha_De_Obra,Remanescente) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        
        $InserirProduto->bindParam(1, $Produtos[$x]);
        $InserirProduto->bindParam(2, $Quantidades[$x]);
        $InserirProduto->bindParam(3, $Data);
        $InserirProduto->bindParam(4, $TypeOps);
        $InserirProduto->bindParam(5, $Dia);
        $InserirProduto->bindParam(6, $Mes);
        $InserirProduto->bindParam(7, $Ano);
        $InserirProduto->bindParam(8, $Destino->Matricula);
        $InserirProduto->bindParam(9, $Operador);
        $InserirProduto->bindParam(10, $NF);
        $InserirProduto->bindParam(11, $Remanescente);
        
        if($Remanescente >= 0){
          $InserirProduto->execute();  
        }
        

       
        $x = $x + 1;

    }
      
    }else{
        $Response = "Tou no Else Mano";
        
    } 
          
      }else{
      
      $Response="Erro, Ficha de Obra Não Encontrada";
          
          
      }
      
      
      
         
          
    
      
      return $Response;
      
        
        
        
        
    }
    
    
    function RemoverProdutosVerificandoStock($Con, $Produto, $Quantidade)
{

    $x = 0;

    foreach ($Produto as $key)
    {

        $VarIN ="Entrada";
    $VarOut = "Saida";
    $Entradas = $Con->prepare("SELECT SUM(Quantidade) as QNTIN FROM stock WHERE Produto=? and Tipo_Operacao=?");
    $Entradas->bindParam(1, $Produto[$x]);
    $Entradas->bindParam(2, $VarIN);
    $Entradas->execute();
    $Entry = $Entradas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Entrada = $Entry->QNTIN;
    
    
    $Saidas = $Con->prepare("SELECT SUM(Quantidade) as QNTOUT FROM stock WHERE Produto=? and Tipo_Operacao=?");
    $Saidas->bindParam(1, $Produto[$x]);
    $Saidas->bindParam(2, $VarOut);
    $Saidas->execute();
    $Out = $Saidas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Saida = $Out->QNTOUT;
    
    $QuabtidadeExistente = $Quantidade_de_Entrada-$Quantidade_de_Saida;
    
    
    if($QuabtidadeExistente >= $Quantidade[$x]){
        //Retira Produto
        
        $Produto_a_Retirar[] = $Produto[$x];
        $Quantidade_a_Retirar[] = $Quantidade[$x];
        
        
        
    }elseif($QuabtidadeExistente < $Quantidade[$x] && $QuabtidadeExistente >= 1){
        //Actualizar Quantidadee retirar do Stock
        
                $Remanescetnte = $Quantidade[$x] - $QuabtidadeExistente;
                
                $Produto_a_Retirar[] = $Produto[$x];
                $Quantidade_a_Retirar[] = $QuabtidadeExistente;
                
                $Produto_a_Requisitar[] = $Produto[$x];
                $Quantidade_a_Requisitar[]= $Remanescetnte;
                
       
        
            }else{
                
                //Manter Produto e Quantidade
                $Produto_a_Requisitar[] = $Produto[$x];
                $Quantidade_a_Requisitar[]= $Quantidade[$x];
                
                
                
                
            }

        
                     
        

        $x = $x + 1;

    }
    
    $new[] = $Produto_a_Retirar;
    $new[] = $Quantidade_a_Retirar; 
    $new[] = $Produto_a_Requisitar;
    $new[] = $Quantidade_a_Requisitar;

    return $new;

}


function CalcularQuantidade($Quantidades){
    
    $Quantidade=0;
    
    foreach($Quantidades as $key){
        
        $Quantidade +=$key;
        
        
    }
    
    return $Quantidade;
    
}



function AddProjectosAbastecidos($Con, $Rotas, $Quantidades, $Ordem, $Justificativa, $Responsavel, $TipoCombustivel, $Preco){
    
    $data = date('d-m-Y');
    $dia = date('d');
    $mes = date('m');
    $ano = date('Y');
    
    $x = 0;
    foreach($Rotas as $key){
        
        $Inserir = $Con->prepare("INSERT INTO GastosPorRota(Rota, Quantidade_Abastecida, Justificativa, Codigo_Ordem, Data, Dia, Mes, Ano, Responsavel,Tipo_De_Combustivel,Preco) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        
        
        $Inserir->bindParam(1, $Rotas[$x]);
        $Inserir->bindParam(2, $Quantidades[$x]);
        $Inserir->bindParam(3, $Justificativa[$x]);
        $Inserir->bindParam(4, $Ordem);
        $Inserir->bindParam(5, $data);
        $Inserir->bindParam(6, $dia);
        $Inserir->bindParam(7, $mes);
        $Inserir->bindParam(8, $ano);
        $Inserir->bindParam(9, $Responsavel);
        $Inserir->bindParam(10, $TipoCombustivel);
        $Inserir->bindParam(11, $Preco);
        $Inserir->execute();
        
        $x++;
  
        
        
        
        
    }
    
    
    
}


function BuscarRotasQueOperarm($Con, $Mes){
    
    
    $Buscar = $Con->prepare("SELECT DISTINCT(Rota) FROM GastosPorRota WHERE Mes=?");
    $Buscar->bindParam(1, $Mes);
    $Buscar->execute();
    
    return $Buscar;
}


function BuscarPrecoPeloTipoDeCombustivel($Con, $Tipo){
    $Preco;
    $Buscar = $Con->prepare("SELECT * FROM CombustivelPreco");
    $Buscar->execute();
    $C = $Buscar->fetch(PDO::FETCH_OBJ);
    
    if($Tipo == "GASOLINA"){
        
       $Preco = $C->GASOLINA; 
    }elseif($Tipo == "DIESEL"){
        
        $Preco = $C->DIESEL; 
    }else{
        
        $Preco = 0.0; 
    }
    
    
}


function BuscarNomeRotasAbastecidasPorMes($Con, $Mes){
    
    $Buscar = $Con->prepare("SELECT DISTINCT(Rota) as NomeRota FROM GastosPorRota WHERE Mes=?");
    $Buscar->bindParam(1, $Mes);
    $Buscar->execute();
    
    
    return $Buscar;
    
}

function BuscarQuantidadeesAbstecidasPorRotas($Con, $Nome, $Mes){
    $Buscar =$Con->prepare("SELECT SUM(Quantidade_Abastecida) AS QNT FROM GastosPorRota WHERE Rota=? and Mes=?");
    $Buscar->bindParam(1, $Nome);
    $Buscar->bindParam(2, $Mes);
    $Buscar->execute();
    
    $C=$Buscar->fetch(PDO::FETCH_OBJ);
    
    return  $C->QNT;
    
    
}

function FormacaoDeSimplesArray($Array){
    
    foreach($Array as $key){
        
        $ArrayResult[] = $key;
        
    }
    
    return $ArrayResult;
}




function BuscarQuantidadeExistenteDeUmProduto($Con,$Produto){
 
       $Data=date('d-M-Y');
    $Type="Saida";
    $Dia=date('d');
    $Mes=date('m');
    $Ano=date('Y');
    $VarIN ="Entrada";
    $VarOut = "Saida";
    $Entradas = $Con->prepare("SELECT SUM(Quantidade) as QNTIN FROM stock WHERE Produto=? and Tipo_Operacao=?;");
    $Entradas->bindParam(1, $Produto);
    $Entradas->bindParam(2, $VarIN);
    $Entradas->execute();
    $Entry = $Entradas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Entrada = $Entry->QNTIN;
    
    
    $Saidas = $Con->prepare("SELECT SUM(Quantidade) as QNTOUT FROM stock WHERE Produto=? and Tipo_Operacao=?;");
    $Saidas->bindParam(1, $Produto);
    $Saidas->bindParam(2, $VarOut);
    $Saidas->execute();
    $Out = $Saidas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Saida = $Out->QNTOUT;
    
    $QuabtidadeExistente = $Quantidade_de_Entrada-$Quantidade_de_Saida;
    
    
    
    return $QuabtidadeExistente;
}


function AdicionarRemocaoNoStock($Con, $Produto,$Quantidade,$Operador,$FichaDeObra,$Destino){
    
    $Data=date('d-M-Y');
    $Type="Saida";
    $Dia=date('d');
    $Mes=date('m');
    $Ano=date('Y');
    $VarIN ="Entrada";
    $VarOut = "Saida";
    $Entradas = $Con->prepare("SELECT SUM(Quantidade) as QNTIN FROM stock WHERE Produto=? and Tipo_Operacao=?;");
    $Entradas->bindParam(1, $Produto);
    $Entradas->bindParam(2, $VarIN);
    $Entradas->execute();
    $Entry = $Entradas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Entrada = $Entry->QNTIN;
    
    
    $Saidas = $Con->prepare("SELECT SUM(Quantidade) as QNTOUT FROM stock WHERE Produto=? and Tipo_Operacao=?;");
    $Saidas->bindParam(1, $Produto);
    $Saidas->bindParam(2, $VarOut);
    $Saidas->execute();
    $Out = $Saidas->fetch(PDO::FETCH_OBJ);
    $Quantidade_de_Saida = $Out->QNTOUT;
    
    $QuabtidadeExistente = $Quantidade_de_Entrada-$Quantidade_de_Saida;
    
    
    if($QuabtidadeExistente >= $Quantidade){
        
        $Remanescente = $QuabtidadeExistente-$Quantidade;
    
    $Inserir = $Con->prepare("INSERT INTO stock(Produto, Quantidade, Data_Operacao, Tipo_Operacao, Dia, Mes, Ano, Destino, Operador,Ficha_De_Obra,Remanescente) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
    $Inserir->bindParam(1, $Produto);
    $Inserir->bindParam(2, $Quantidade);
    $Inserir->bindParam(3, $Data);
    $Inserir->bindParam(4, $Type);
    $Inserir->bindParam(5, $Dia);
    $Inserir->bindParam(6, $Mes);
    $Inserir->bindParam(7, $Ano);
    $Inserir->bindParam(8, $Destino);
    $Inserir->bindParam(9, $Operador);
    $Inserir->bindParam(10, $FichaDeObra);
    $Inserir->bindParam(11, $Remanescente);
    $Inserir->execute();
    
          
    
    }
}


function BuscarNomeDoUserPeloID($Con, $ID){
    $Buscar = $Con->prepare("SELECT * FROM users WHERE id_user=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();
    
    $C = $Buscar->fetch(PDO::FETCH_OBJ);
    
    return $C->nome." ".$C->Snome;
}


function sendMailWithMiltipleAttachmets($to, $subject, $message, $senderEmail, $senderName, $files = array()){ 
    // Sender info  
    $from = $senderName." <".$senderEmail.">";  
    $headers = "From: $from"; 
 
    // Boundary  
    $semi_rand = md5(time());  
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";  
 
    // Headers for attachment  
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";  
 
    // Multipart boundary  
    $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" . 
    "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";  
 
    // Preparing attachment 
    if(!empty($files)){ 
        for($i=0;$i<count($files);$i++){ 
            if(is_file($files[$i])){ 
                $file_name = basename($files[$i]); 
                $file_size = filesize($files[$i]); 
                 
                $message .= "--{$mime_boundary}\n"; 
                $fp =    @fopen($files[$i], "rb"); 
                $data =  @fread($fp, $file_size); 
                @fclose($fp); 
                $data = chunk_split(base64_encode($data)); 
                $message .= "Content-Type: application/octet-stream; name=\"".$file_name."\"\n" .  
                "Content-Description: ".$file_name."\n" . 
                "Content-Disposition: attachment;\n" . " filename=\"".$file_name."\"; size=".$file_size.";\n" .  
                "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n"; 
            } 
        } 
    } 
     
    $message .= "--{$mime_boundary}--"; 
    $returnpath = "-f" . $senderEmail; 
     
    // Send email 
    $mail = mail($to, $subject, $message, $headers, $returnpath);  
     
    // Return true if email sent, otherwise return false 
    if($mail){ 
        return true; 
    }else{ 
        return false; 
    } 
}

function InsertFornecedores($Con, $Nome, $Contacto, $Email, $Nuit, $Localizacao, $Pais, $Cidade, $Cpost){
    
    $Insert = $Con->prepare("INSERT INTO Fornecedores(Nome_Fornecedor, Cotacto_Fornecedor, Email_Fornecedor, NUIT_Fornecedor, Localizacao, Pais, Cidade, Codigo_Postal) VALUES (?,?,?,?,?,?,?,?)");
    $Insert->bindParam(1, $Nome);
    $Insert->bindParam(2, $Contacto);
    $Insert->bindParam(3, $Email);
    $Insert->bindParam(4, $Nuit);
    $Insert->bindParam(5, $Localizacao);
    $Insert->bindParam(6, $Pais);
    $Insert->bindParam(7, $Cidade);
    $Insert->bindParam(8, $Cpost);
    
    if($Insert->execute()){
        
        return 200;
        
    }else{
        return 500;
        
    }

    
    
    
}


function BuscarCodigodaRequisicao($Con, $Numero){
    
    $Buscar = $Con->prepare("SELECT * FROM REQUISICOES WHERE ID_REQUISICOES=?");
    $Buscar->bindParam(1, $Numero);
    $Buscar->execute();
    
    $C = $Buscar->fetch(PDO::FETCH_OBJ);
    
    return ($C->Numero_Requisicao);
    
    
}

function InnserirTarefa($Con, $Titulo, $Descricao, $Tarefas, $Anexos, $Mandante, $Executor, $DataLimite){
    

    
    $Tar = json_encode($Tarefas);
    $Ane = json_encode($Anexos);
    $Inserir = $Con->prepare("INSERT INTO Tarefas(Titulo, Descricao, Tarefas_a_executar, Anexos, Mandante, Executor, data_limite) VALUES (?,?,?,?,?,?,?)");
    $Inserir->bindParam(1, $Titulo);
    $Inserir->bindParam(2, $Descricao);
    $Inserir->bindParam(3, $Tar);
    $Inserir->bindParam(4, $Ane);
    $Inserir->bindParam(5, $Mandante);
    $Inserir->bindParam(6, $Executor);
    $Inserir->bindParam(7, $DataLimite);

    
    
    if($Inserir->execute()){
        
        return $Con->lastInsertId();
        
    }else{
        
        return null;
        
    }
    
    
}


function CriarUtilizador($Con, $Email, $Senha, $Nome, $TypUser, $Cargo,$Departamento){
    
    $BuscarEmails = $Con->prepare("SELECT email from users WHERE email=?");
    $BuscarEmails->bindParam(1, $Email);
    $BuscarEmails->execute();
    
    
    if($BuscarEmails->rowCount() === 0){
    
    $Inserir = $Con->prepare("INSERT INTO users(nome, ency_password, user_type, email, Cargo, Departamento) VALUES (?,?,?,?,?,?)");
    
    $Inserir->bindParam(1, $Nome);
    $Inserir->bindParam(2, encrypt_decrypt('encrypt',$Senha));
    $Inserir->bindParam(3, $TypUser);
    $Inserir->bindParam(4, $Email);
    $Inserir->bindParam(5, $Cargo);
    $Inserir->bindParam(6, $Departamento);
    
        if($Inserir->execute()){
            
            return 201;
            
            
        }else{
            
            return 401;
            
        }
    }else{
        
        return 501;
        
    }
    
    
    
}



function GeradorDeCaracteresAleatorios($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function LogIn($Connection, $username, $password){



$SearchUser=$Connection->prepare("SELECT * FROM users WHERE email=? and ency_password=?");
$SearchUser->bindParam(1,$username);
$SearchUser->bindParam(2,$password);
$SearchUser->execute();


if($SearchUser->rowCount() >=1){

while ($carregar = $SearchUser->fetch(PDO::FETCH_OBJ)) {

    if($username == $carregar->email  and $password == $carregar->ency_password){
      
      return $Dados_User= array("NomeUser"=>$username,
                                    "Tipo"=>$carregar->user_type,
                                    "Nome"=>$carregar->nome,
                                    "SobreNome"=>$carregar->Snome,
                                    "Email"=>$carregar->email,
                                    "Acesso"=>$carregar->acesso,
                                    "Cargo"=>$carregar->Cargo,
                                    "Avatar"=>$carregar->Avatar,
                                    "IdUsuario"=>$carregar->id_user,
                                    "Level"=>$carregar->Level,
                                    "DeepID"=>$carregar->Departamento
                                    );
    }else{
      return $Dados_User= array();
    }
}

}else{
return $Dados_User= array();

}


}

function InserirActualizacaoDeTarefa($Con, $Descricao, $Observacoes, $Anexos, $Id_Tarefa){
    
    $Inserir = $Con->prepare("INSERT INTO actualizacao_tarefa(Descricao, Obsercacoes, anexos, id_tarefa) VALUES (?,?,?,?)");
    $Inserir->bindParam(1, $Descricao);
    $Inserir->bindParam(2, $Observacoes);
    $Inserir->bindParam(3, $Anexos);
    $Inserir->bindParam(4, $Id_Tarefa);
    
    if($Inserir->execute()){
        NumeroDeActualizacoesEmTarefas($Con, $Id_Tarefa);
        return 200;
    }else{
       return 400; 
    }
    
    
}

function NumberOfNotAllowedExtensions($Files, $AllowedExtensions){
    $NumberOfNotAllowrdExtensions=0;
    foreach ($Files as $path)
{
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $UploadExtensions[] = strtolower($ext);

}

foreach ($UploadExtensions as $Upextensions)
{

    if (!in_array($Upextensions, $AllowedExtensions))
    {
        $NumberOfNotAllowrdExtensions += 1;
    }
    
    
    return $NumberOfNotAllowrdExtensions;
}
}

function ExtensoesPermitidas($AllowedExtensions){
    $AllowedExtensionsTXT = "";
    if(count($AllowedExtensions) > 0 && count($AllowedExtensions) === 1){
        
            foreach ($AllowedExtensions as $allowed)
                {
                
                    $AllowedExtensionsTXT .= $allowed ;
                
                }
        
    }elseif(count($AllowedExtensions) > 1){
        
            $Counter = count($AllowedExtensions);
            foreach ($AllowedExtensions as $allowed)
            {
            
                if ($Counter === 1)
                {
                    $AllowedExtensionsTXT .= " ou " . $allowed;
                }elseif($Counter === 2){
                    $AllowedExtensionsTXT .= $allowed ;
                }
                else
                {
                    $AllowedExtensionsTXT .= $allowed . ",";
                }
            
                $Counter -= 1;
            
            }
        
    }else{
        
        
    }
    return $AllowedExtensionsTXT;
}


function UploadMultiplesFiles($Diretorio){
    $countfiles = count($_FILES['Anexos']['name']);

            for ($i = 0;$i < $countfiles;$i++)
            {
                $filename = $_FILES['Anexos']['name'][$i];
                move_uploaded_file($_FILES[Anexos][tmp_name][$i], $Diretorio . $filename);

            }
}

function NumeroDeActualizacoesEmTarefas($Con, $IdTarefa){
    
    $Update = $Con->prepare("UPDATE Tarefas set Actualizacoes = Actualizacoes+1 WHERE id=?");
    $Update->bindParam(1, $IdTarefa);
    $Update->execute();
    
}

function UpdateStatusTarefa($Con, $NumberStatus, $ID){
    
    $Update = $Con->prepare("UPDATE Tarefas SET Status=? WHERE id=?");
    $Update->bindParam(1, $NumberStatus);
    $Update->bindParam(2, $ID);
    
    if($NumberStatus == 2){
      $UPD = $Con->prepare("UPDATE Tarefas SET data_entrega=? WHERE id=?");
    $UPD->bindParam(1, date('Y-m-d'));
    $UPD->bindParam(2, $ID);  
    $UPD->execute();
    }
    
    if($Update->execute()){
        return 200;
    }else{
        return 400;
    }
    
    
}

function NumeroDeActualizacoesTarefa($Con, $ID){
    
    
    $Buscar = $Con->prepare(" SELECT * FROM actualizacao_tarefa WHERE id_tarefa=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();
    
    return ($Buscar->rowCount());
    
}


function DadosActualizacaoTarefa($Con, $ID){
    $Buscar = $Con->prepare(" SELECT * FROM actualizacao_tarefa WHERE id_tarefa=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();
    
    return $Buscar;
    
}


function EmailMandatarioTarefa($Con, $ID){
    
    $Buscar = $Con->prepare("SELECT * FROM Tarefas WHERE id=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();
    
    $C = $Buscar->fetch(PDO::FETCH_OBJ);
    
    return $C->Mandante;
    
    
}



function EmailExecutorTarefa($Con, $ID){
    
    $Buscar = $Con->prepare("SELECT * FROM Tarefas WHERE id=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();
    
    $C = $Buscar->fetch(PDO::FETCH_OBJ);
    
    return $C->Executor;
    
    
}
function BuscaDadosDeTarefa($Con, $ID){
    $Buscar = $Con->prepare("SELECT * FROM Tarefas WHERE id=?");
    $Buscar->bindParam(1, $ID);
    $Buscar->execute();
 
    
    return $Buscar;
    
}

function ActualizarContagemBomba($Con, $Quantidade){
    
    $Update = $Con->prepare("UPDATE request SET Contagem_Bomba=Contagem_Bomba+?");
    $Update->bindParam(1, $Quantidade);
    $Update->execute();
    
    
    
}

function GEtContagemBomba($Con){
    
    $Buscar = $Con->prepare("SELECT Contagem_Bomba FROM request");
    $Buscar->execute();
    $C = $Buscar->fetch(PDO::FETCH_OBJ);
    return $C;
}

function TodosPagamentos($Con){
    
    $Buscar = $Con->prepare("SELECT * FROM PagamentoDeRequisicoes");
    $Buscar->execute();
    
    
    return $Buscar;
}

function SemanasPagasRequisicoes($Con, $Ano){
    
    $Buscar = $Con->prepare("SELECT DISTINCT(Semana) AS Semana FROM PagamentoDeRequisicoes WHERE Ano=?");
    $Buscar->bindParam(1, $Ano);
    $Buscar->execute();
    
    return $Buscar;
    
    
}


function PagamentosDaSemana($Con, $Ano, $Semana){
    
    $Buscar = $Con->prepare("SELECT * FROM PagamentoDeRequisicoes WHERE Ano=? and Semana=?");
    $Buscar->bindParam(1, $Ano);
    $Buscar->bindParam(2, $Semana);
    $Buscar->execute();
    
    return $Buscar;
    
    
}


function DetalhesPagamentoRequisicao($DetalhesPagamento){
    $ImprimirDetalhes ="<h5 class='text-dark mb-1'><b>Detalhes De Pagamento<b/></h5>";
$DetalhesEspecificos;
$cheque;
$especificditails;

foreach($DetalhesPagamento as $key){
    
    

    
    
        
        $ValorPago = number_format($key[3], 2, ',', ' ')." ".$key[5];
        $Remanescente = number_format($key[4], 2, ',', ' ')." ".$key[5];
        
        $especificditails = json_decode($key[2],true);
        
       
        $Numerario="";
        $cheque="";
        $Transferencia="";

        
                
        foreach ($especificditails as &$valor) 
                {
                    if($valor["TipoNumerario"] != "")
                    {
                        $Numerario .= "Metodo de Pagamento: <b>".$valor["TipoNumerario"].",Pagamento em Numerario</b> Valor Pago: <b>". number_format($valor["ValorPagoNumerario"], 2, ',', ' ')." ".$key[5]."</b> |";
                    }else{
                        
                        $Numerario .="";
                    }
                    
                    
                    if($valor["TipoCheque"] != "")
                    {
                      $cheque .= "Metodo de Pagamento: <b>".$valor["TipoCheque"].", Numero do Cheque: <b>".$valor["NumeroCheque"].", </b> Banco: <b>".$valor["BancoCheque"]." ,</b> Valor Pago: <b>". number_format($valor["ValorPagoCheque"], 2, ',', ' ')." ".$key[5]."<b> |";
                    }else{
                         $cheque .= "";
                        
                    }
                    
                    
                    if($valor["TipoTransferencia"] != "")
                    {
                        $Transferencia .= "Metodo de Pagamento: <b>".$valor["TipoTransferencia"]."</b>, Referência Transfência: <b>".$valor["ReferenciaTransferencia"].", </b> Banco: <b>".$valor["BancoTransferencia"].", </b> Valor Pago: <b>". number_format($valor["ValorPagoTransferencia"], 2, ',', ' ')." ".$key[5]." |";
                    }else{
                        
                        $Transferencia .="";
                        
                        
                    }
                    
                    
                    
                    
                }
                
         
                        
        
       
     if(count($DetalhesPagamento) >=2){   
        
       $ImprimirDetalhes .=" <div class='col-sm-6'></div>
                     
                     
                    
                     <div><b style='color:red'>$key[8]</b></div>
                     <div>Data de Pagaemento: <b>$key[0]</b></div>
                     <div>Tipo de Pagamento: <b>$key[1] </b> => $Numerario $Transferencia $cheque</div>
                     <div>Total Pago: <b>$ValorPago </div>
                     <div>Remanescente: <b>$Remanescente</b></div>
                     <div>Operador: <b>$key[9]</b></div>
                 <br>";
        
        
    }else{
        $ImprimirDetalhes .=" <div class='col-sm-6'></div>
                     
                     
                    
                     
                     <div>Data de Pagaemento: <b>$key[0]</b></div>
                     <div>Tipo de Pagamento: <b>$key[1] </b> => $Numerario $Transferencia $cheque
                     
                     
                     </div>
                     <div>Total Pago: <b>$ValorPago </div>
                     <div>Remanescente: <b>$Remanescente</b></div>
                     <div>Operador: <b>$key[8]</b></div>
                 <br>";
        
        
    }
    
    return $ImprimirDetalhes;
}
}


function VarificarExisteciaSimulacao($Con, $Codigo){
    
    
    $Verify = $Con->prepare("SELECT * FROM SimulacaoCG WHERE Codigo_Simulacao=?");
    $Verify->bindParam(1, $Codigo);
    $Verify->execute();
    
    return $Verify->rowCount();
}

function IserirSimulacao($Con, $Cliente, $Referencia, $operador, $Codigo, $SimulacaoJSON){
    
    $Inserir = $Con->prepare("INSERT INTO SimulacaoCG(Cliente, Referencia, operador, Codigo_Simulacao, SimulacaoJSON) VALUES (?,?,?,?,?)");
    
    $Inserir->bindParam(1, $Cliente);
    $Inserir->bindParam(2, $Referencia);
    $Inserir->bindParam(3, $operador);
    $Inserir->bindParam(4, $Codigo);
    $Inserir->bindParam(5, json_encode($SimulacaoJSON));
    
    
    if($Inserir->execute()){
        return 200;
    }else{
        return 400;
    }
}


function UpdateSimulacao($Con, $Cliente, $Referencia, $SimulacaoJSON, $Codigo){
    
    $Update = $Con->prepare("UPDATE SimulacaoCG SET Cliente=?,Referencia=?,SimulacaoJSON=? WHERE Codigo_Simulacao=?");
    $Update->bindParam(1, $Cliente);
    $Update->bindParam(2, $Referencia);
    $Update->bindParam(3, json_encode($SimulacaoJSON));
    $Update->bindParam(4, $Codigo);
    
    
    if($Update->execute()){
        return 200;
    }else{
        return 400;
    }
    
    
}

function BuscarCodigoSimulacao($Con){
    
    $Buscar = $Con->prepare("SELECT Numero_de_Simulacoes FROM request");
    $Buscar->execute();
    $C = $Buscar->fetch(PDO::FETCH_OBJ);
    
    return $C->Numero_de_Simulacoes;
    
}

function UpdateCodigoSimulacao($Con){
    $Update = $Con->prepare("UPDATE request SET Numero_de_Simulacoes=Numero_de_Simulacoes+1");
    
    if($Update->execute()){
        return 200;
    }else{
        
        return 400;
    }
}

function AllDrivers($Connection){
    
    $Find = $Connection->prepare("SELECT * FROM Colaboradores WHERE TipoColaborador='Motorista'");
    $Find->execute();
    
    $Data;
    while($Loop = $Find->fetch(PDO::FETCH_OBJ)){
        
        $Data [] =  array('id'=>$Loop->ID_Colaborador, 'name'=>$Loop->Nome_Motorista, 'surname'=>$Loop->SobreNome_Motorista, 'active'=>$Loop->Estado, 'licenceDriver'=>$Loop->Carta_de_Conducao);
    }
    
    return json_encode($Data);
}


function AllCars($Connection){
    
    $FindCar = $Connection->prepare("SELECT * FROM viatura");
    $FindCar->execute();
    
    $Data;
    while($LoopCar = $FindCar->fetch(PDO::FETCH_OBJ)){
        
        if(strlen($LoopCar->Matricula) <= 10)
        {
            $Data [] =  array('combustibleType'=>$LoopCar->TipodeCombustivel, 'literByquilometer'=>$LoopCar->LitrosPorQuilometro, 'lotation'=>$LoopCar->Capacidade, 'manufYear'=>$LoopCar->Ano_Fabrico, 'mark'=>$LoopCar->Marca, 'model'=>$LoopCar->Modelo, 'nrChassi'=>$LoopCar->NChasi, 'nrEngine'=>$LoopCar->NMotor, 'status'=>$LoopCar->Active, 'vRegist'=>$LoopCar->Matricula);
        }
        
        
    }
    
    return json_encode($Data);
}


function Departamentos($Con){
    
    $Buscar = $Con->prepare("SELECT * FROM Departamento");
    $Buscar->execute();
    
    
    return $Buscar;
    
}




function ListaUtilizadores($Con){
    
    $Buscar = $Con->prepare("SELECT * FROM users");
    $Buscar->execute();
    
    return $Buscar;
}

function VerificarExistenciaEmailUtilizador($Con, $Email){
    
    $Buscar = $Con->prepare("SELECT * FROM users WHERE email=?");
    $Buscar->bindParam(1, $Email);
    $Buscar->execute();
    
    return $Buscar->rowCount();
    
}

function ValidarEmail($Email){
    
        if (filter_var($Email, FILTER_VALIDATE_EMAIL)) {
         return true;
        } else {
           return false;
        }

}

function TipoUtilizadores($Con){
    
    $Buscar = $Con->prepare("SELECT DISTINCT(user_type) FROM users");
    $Buscar->execute();
    
    return $Buscar;
}

function ComparacaoSenhaActual($Con, $Senha, $IDUser){
    
    $Buscar = $Con->prepare("SELECT * FROM users WHERE id_user=? and ency_password=?");
    $Buscar->bindParam(1, $IDUser);
    $Buscar->bindParam(2, encrypt_decrypt('encrypt',$Senha));
    $Buscar->execute();
    
    return $Buscar->rowCount();
    
    
}

function ActualizarSenha($Con, $Senha, $ID){
    $Update = $Con->prepare("UPDATE users SET ency_password=? WHERE id_user=?");
    $Update->bindParam(1, encrypt_decrypt('encrypt',$Senha));
    $Update->bindParam(2, $ID);
    
    
    if($Update->execute()){
        return 200;
    }else{
        
        return 500;
        
    }
    
    
}


?>
