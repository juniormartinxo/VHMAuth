<?php

namespace MJP;
use Exception;

/**
 * Classe VHMAuth
 * Authentica e interage com a plataforma de gerenciamento de servidores de hospedagem VHM/ CPanel
 *
 * @author    Aluisio Martins Junior <junior@mjpsolucoes.com.br>
 * @namespace Classes\VHMAuth
 * @copyright 2014 MJP Soluções
 * @see       http://docs.cpanel.net/twiki/bin/view/SoftwareDevelopmentKit/ApiAuthentication
 *            https://documentation.cpanel.net/display/SDK/WHM+API+1+Functions#WHMAPI1Functions-JSONoutputformat
 *            http://docs.cpanel.net/twiki/bin/view/SoftwareDevelopmentKit/WebHome#CpanelApi
 *            http://docs.cpanel.net/twiki/bin/view/AllDocumentation/WHMDocs/RemoteAccess
 *            https://documentation.cpanel.net/display/ALD/Mail+Delivery+Reports
 *            https://documentation.cpanel.net/display/SDK/WHM+API+1+-+emailtrack_search
 * @version   1.1.0
 */

class VHMAuth {
    private $_urlVhm;
    private $_usrVhm;
    private $_hshVhm;
    private $_qryVhm;
    private $_erros = array(
                        'url_empty'  => 'Defina a url de conexão com o VHM',
                        'user_empty' => 'Defina um usuário válido no VHM',
                        'hash_empty' => 'Defina o hash para consexão do VHM!'
                    );

    /**
     * Construct
     *
     * @param string $urlVhm Url onde está instalado o VHM (exemplo: https://www.meudominio.com:2087)
     * @param string $usrVhm Usuário válido no VHM
     * @param string $hshVhm Hash gerado no VHM
     */
    function __construct($urlVhm, $usrVhm, $hshVhm){
        if(empty($urlVhm)){
            $this->explain($this->_erros['url_empty'],'danger','exit');
        }

        if(empty($usrVhm)){
            $this->explain($this->_erros['user_empty'],'danger','exit');
        }

        if(empty($hshVhm)){
            $this->explain($this->_erros['hash_empty'],'danger','exit');
        }
        $this->setUrl($urlVhm);
        $this->setUsr($usrVhm);
        $this->setHsh($hshVhm);
    }

    /**
     * emailtrack_search()
     * Gera um relatório de entrega de e-mails do VHM
     *
     * @param integer $success      Seta se no relatório irá constar os e-mails que foram enviados com sucesso (0=não,1=sim)
     * @param integer $defer        Seta se no relatório irá constar os e-mails que foram adiados (0=não,1=sim)
     * @param integer $failure      Seta se no relatório irá constar os e-mails que obtiveram falha no envio (0=não,1=sim)
     * @param integer $inprogress   Seta se no relatório irá constar os e-mails que ainda estão sendo enviados (0=não,1=sim)
     * @param string  $deliverytype Seta o tipo de registros de entrega (all=todos os registros, remote=para mensagens enviadas de
     *                              outros servidores e local=mensagens enviados para o servidor do próprio servidor)
     * @param string  $sortField    Campo responsável pela ordenação
     * @param integer $sortReverse  Seta se o relatório será gerado na ordem decrescente (0=crescente,1=decrescente)
     * @param string  $filterField  Campo responsável pelo filtro (sender=Remetente, recipient=Beneficiário, msgid=ID da mensagem,
     *                              domain=Domínio, user=Usuário, senderip=IP do remetente, senderhost=Anfitrião remetente,
     *                              senderauth=Remetente Auth, spamscore=Pontuação Spam, ip=IP entrega, host=Anfitrião de entrega,
     *                              router=Router, transport=Transporte, message=Mensagem resultado, deliveredto=Para entregue,
     *                              ""=Sem Filtro)
     * @param string  $filterType   Seta o tipo do filtro utilizado (begins=Começa com, eq=Exato, contains=Parcial)
     * @param string  $arg          Valor que será envido para pesquisa do filtro
     * @param date    $dateTimeGT   Data inicial do relatório nos formatos "Y/m/d H:i:s" ou "Y-m-d H:i:s"
     * @param date    $dateTimeLT   Data final do relatório nos formatos "Y/m/d H:i:s" ou "Y-m-d H:i:s"
     * @param integer $chunkStart   Primeiro registro que será visualizado apartir da paginação
     * @param integer $chunkSize    Tamanho máximo de registros que serão retornados (valor max=5000)
     *
     * @return string
     */
    public function emailtrack_search($success,$defer,$failure,$inprogress,$deliverytype,$sortField,$sortReverse,$filterField,
                                      $filterType,$arg,$dateTimeGT,$dateTimeLT,$chunkStart,$chunkSize){

        $mktDateTimeGT = strtotime($dateTimeGT);
        $mktDateTimeLT = strtotime($dateTimeLT);

        $url  = 'emailtrack_search?';
        $url .= 'api.version=1';
        $url .= '&success=' . $success;
        $url .= '&defer=' . $defer;
        $url .= '&failure=' . $failure;
        $url .= '&inprogress=' . $inprogress;
        $url .= '&deliverytype=' . $deliverytype;
        $url .= '&api.sort.enable=1';
        $url .= '&api.sort.a.field=' . $sortField;
        $url .= '&api.sort.a.reverse=' . $sortReverse;
        $url .= '&api.filter.enable=1';
        $url .= '&api.filter.verbose=1';
        $url .= '&api.filter.c.field=sendunixtime';
        $url .= '&api.filter.c.type=lt';
        $url .= '&api.filter.c.arg0=' . $mktDateTimeLT;
        $url .= '&api.filter.b.field=sendunixtime';
        $url .= '&api.filter.b.type=gt';
        $url .= '&api.filter.b.arg0=' . $mktDateTimeGT;
        $url .= '&api.filter.a.field=' . $filterField;
        $url .= '&api.filter.a.type=' . $filterType;
        $url .= '&api.filter.a.arg0=' . $arg;
        $url .= '&api.chunk.enable=1';
        $url .= '&api.chunk.verbose=1';
        $url .= '&api.chunk.start=' . $chunkStart;
        $url .= '&api.chunk.size=' . $chunkSize;

        $this->setQry($this->_urlVhm . $url);

        return $this->execute();
    }

    /**
     * execute()
     * Método responsável pela execução do comando solicitado ao VHM
     *
     * @return mixed
     */
    public function execute(){
        $curl = curl_init();
        # Create Curl Object
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);

        # Allow certs that do not match the domain
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);

        # Allow self-signed certs
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);

        # Return contents of transfer on curl_exec
        $header[0] = "Authorization: WHM $this->_usrVhm:" . $this->_hshVhm;

        # Remove newlines from the hash
        curl_setopt($curl,CURLOPT_HTTPHEADER,$header);

        # Set curl header
        curl_setopt($curl, CURLOPT_URL, $this->_qryVhm);

        # Set your URL
        $result = curl_exec($curl);

        # Execute Query, assign to $result
        if ($result == false) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $this->_qryVhm");
        }

        curl_close($curl);

        return $result;
    }

    /**
     * explain()
     * Método responsável por enviar uma mensagem para a página carregada.
     *
     * @param string $msg  Mensagem que será impressa na tela
     * @param string $tipo Seta o tipo de mensagem que será enviada, recebe os seguintes valores:
     *                     success, info, warning, danger
     * @param string exit  Verifica se o script será parado caso a mensagem seja disparada
     *
     * @return boolean
     */
    private function explain($msg,$tipo,$exit=""){
        $backTrace = debug_backtrace();

        $titulo = 'Atenção';
        $icon = 'info';

        switch($tipo){
            case "success":
                $titulo = 'Sucesso';
                $icon = 'check';
            break;
            case "info":
                $titulo = 'Informação';
                $icon = 'info-circle';
            break;
            case "warning":
                $titulo = 'Alerta';
                $icon = 'exclamation-triangle';
            break;
            case "danger":
                $titulo = 'Erro';
                $icon = 'times';
            break;
        }

        $html  = '<style type="text/css">';
        $html .= '.msgExplain {width:85% !important; display:block !important; top:0 !important; right:0 !important; margin:25px auto !important; background:#fff !important; padding:0 15px 15px 15px !important; border:1px solid #DDDDDD !important; color:#2c3e50 !important; z-index:999 !important; font-family:\"MS Serif\", \"New York\", serif !important; line-height:24px !important;}';
        $html .= '.msgExplain h6 {font-weight:bold;font-size:24px !important; margin-bottom:15px !important; font-family:"Courier New", Courier, monospace !important; padding:10px 0 0 0 !important;}';
        $html .= '.msgExplain .clear {clear:both !important;}';
        $html .= '.msgExplain strong {color:#c0392b !important; font-weight:bold !important;}';
        $html .= '.msgExplain pre {white-space: pre-wrap !important; word-wrap: break-word !important; margin-top:25px;}';
        $html .= '.msgExplain .tip0 {color:#e74c3c;font-weight:bold;}';
        $html .= '.msgExplain .tip1 {color:#2c3e50;font-weight:bold;}';
        $html .= '.msgExplain .tip2 {color:#95a5a6;font-style:italic !important;}';
        $html .= '.msgExplain h6.success{color: #3C763D}';
        $html .= '.msgExplain h6.info{color: #31708F}';
        $html .= '.msgExplain h6.warning{color: #8A6D3B}';
        $html .= '.msgExplain h6.danger{color: #A94442}';
        $html .= '</style>';
        $html .= '<div class="msgExplain">';
        $html .= '<h6 class="' . $tipo . '"><i class="fa fa-' . $icon . '"></i> ' . $titulo . '</h6>';
        $html .= '<p>';
        $html .= '<span class="tip2">#Arquivo:</span> ' . $backTrace[0]['file'] . '<br />';
        $html .= '<span class="tip2">#Linha:</span> ' . $backTrace[0]['line'] . '<br />';
        $html .= '</p>';
        $html .= '<pre class="alert alert-' . $tipo . '" role="alert">';
        $html .= '<i class="fa fa-long-arrow-right"></i> ' . $msg;
        $html .= '</pre>';
        $html .= '<div class="clear"></div>';
        $html .= '</div>';

        echo $html;

        if(!empty($exit)){
            exit;
        }

        return true;
    }

    /**
     * debug()
     * Método responsável por debugar resultados
     *
     * @param mixed  $var  Qualquer tipo de variável
     * @param string $exit verifica se o script será parado caso o método seja disparado
     *
     * return boolean
     */
    public function debug($var,$exit=""){
        $backTrace = debug_backtrace();

        $vars = get_defined_vars();

        $typeVar = gettype($var);

        echo '<style type="text/css">
                .msgDebug {width:100% !important; display:block !important; top:0 !important; right:0 !important; margin:25px auto !important; background:#fff !important; padding:0 15px 15px 15px !important; border:1px solid #DDDDDD !important; color:#2c3e50 !important; z-index:999 !important; font-family:\"MS Serif\", \"New York\", serif !important; line-height:24px !important;}
                .msgDebug h6 {font-weight:bold;font-size:24px !important; margin-bottom:15px !important; font-family:"Courier New", Courier, monospace !important; padding:10px 0 0 0 !important;}
                .msgDebug .clear {clear:both !important;}
                .msgDebug strong {color:#c0392b !important; font-weight:bold !important;}
                .msgDebug pre {white-space: pre-wrap !important; word-wrap: break-word !important;}
                .msgDebug .tip0 {color:#e74c3c;font-weight:bold;}
                .msgDebug .tip1 {color:#A94442;font-weight:bold;}
                .msgDebug .tip2 {color:#95a5a6;font-style:italic !important;}
              </style>
              <div class="msgDebug">
              <h6>DEBUG</h6>
              <p>
                <span class="tip2">#Arquivo:</span> ' . $backTrace[0]['file'] . '<br />
                <span class="tip2">#Linha:</span> ' . $backTrace[0]['line'] . '<br />
                <span class="tip2">#Tipo da variável:</span> ' . $typeVar . '<br />
              </p>
              <br />
              <pre>';

        if(is_scalar($var)){
            $txt = preg_replace('/\s\s+/', ' ', $var);
            $arrSQL_A = array("/SELECT/", "/FROM/", "/WHERE/", "/AND/", "/ORDER BY/", "/OR/", "/LIMIT/", "/IN/", "/LIKE/", "/ASC/");
            $arrSQL_B = array("<strong>SELECT</strong>","\n<strong>FROM</strong>", "\n<strong>WHERE</strong>", "\n<strong>AND</strong>", "<strong>ORDER BY</strong>", "\n<strong>OR</strong>", "\n<strong>LIMIT</strong>","<strong>IN</strong>","<strong>LIKE</strong>","<strong>ASC</strong>");

            echo preg_replace($arrSQL_A, $arrSQL_B, $txt);
        }else{
            $text = serialize($var);
            $text = unserialize($text);
            $text = print_r($text, true);

            $arr_A = array(
                    '/Array/',
                    '/\[/',
                    '/\]/',
                    '@<script[^>]*?>.*?</script>@si',
                    '@<[\/\!]*?[^<>]*?>@si',
                    '@<style[^>]*?>.*?</style>@siU',
                    '@<![\s\S]*?--[ \t\n\r]*>@'
            );

            $arr_B = array(
                    '<strong>Array</strong>',
                    '[<span class=\"tip0\">',
                    '</span>]',
                    '',
                    '',
                    '',
                    ''
            );

            $text = preg_replace($arr_A, $arr_B, $text);

            echo $this->debug_color($text);
        }

        echo '</pre>
              <div class="clear"></div>
              </div>';

        if(!empty($exit)){
            exit;
        }

        return true;
    }

    /**
     * debug_color()
     * Método para colorir o debug
     *
     * @access private
     *
     * @param  string info to colorize
     *
     * @return string HTML colorida
     */
    private function debug_color($str){
        $str = preg_replace("/\[(\w*)\]/i", '[<font color="red">$1</font>]', $str);
        $str = preg_replace("/(\s+)\)$/", '$1)</span>', $str);
        $str = str_replace('Array','<font color="blue">Array</font>',$str);
        $str = str_replace('=>','<font color="#556F55">=></font>',$str);

        return $str;
    }

    private function setUrl($url){
        $this->_urlVhm = $url;
    }

    private function setUsr($usr){
        $this->_usrVhm = $usr;
    }

    private function setHsh($hsh){
        $hsh = str_replace(' ','',$hsh);
        $this->_hshVhm = preg_replace("'(\r|\n)'","",$hsh);
    }

    private function setQry($url){
        $this->_qryVhm = $url;
    }

    private function getUsr(){
        return $this->_usrVhm;
    }

    private function getHsh(){
        return $this->_hshVhm;
    }

    private function getQry(){
        return $this->_qryVhm;
    }

    /**
     */
    function __destruct() {
    }
}
