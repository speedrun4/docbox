<?php
// include_once ('Mail.php');
// include_once ('Mail/mime.php');
use Docbox\control\Controller;
use Docbox\model\User;

include_once (dirname(__FILE__) . "/Controller.php");

class RecoverPasswordController extends Controller {
    /**
     * @param User $user
     * @param string $token
     * 
     * @return bool
     */
    function insertToken($user, $token) {
        $query = "INSERT INTO change_pass_tokens(tok_user, tok_token) VALUES(?, ?)";

        if($stmt = $this->db->prepare($query)) {
            if($stmt->bind_param("is", $user->id, $token)) {
                if($stmt->execute()) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    function getTokenIfValid($token) {
        $query = "SELECT *,timediff(CURRENT_TIMESTAMP, tok_when) as diff_time from change_pass_tokens where tok_token like '$token' and tok_dead = false";

        if($result = $this->db->query($query)) {
            if($row = $result->fetch_object()) {
                if($row->diff_time < 72) {
                    return $row;
                }
            }
        }

        return NULL;
    }
    
    function invalidateToken($tok_id) {
        $query = "UPDATE change_pass_tokens SET tok_dead = TRUE WHERE tok_id = $tok_id";
        if($this->db->query($query)) {
            return TRUE;
        }
        return FALSE;
    }

    function sendRecoverEmail($username, $email, $link) {
        if(!empty($username) && !empty($email)) {
            $server = $_SERVER['SERVER_NAME'];
            // Constructing the email
            $sender = "DocBox <noreply@$server>";// Your name and email address
            $recipient = $username . ' <'. $email . '>'; // The Recipients name and email address
            $subject = "Recuperação de senha"; // Subject for the email
            $text = "Olá $username, para efetuar a troca de senha por favor copie e cole a seguinte URL no seu navegador: $link."; // Text version of the email
            $html = "<html><body><p>Ol&aacute; $username ,</p>".
                "<p>Recebemos sua solicita&ccedil;&atilde;o de troca de senha.</p>".
                "<p>Para efetuar a troca de senha, clique <a href='$link'>aqui</a>.</p>".
                "<p><code>Caso o link n&atilde;o abra por favor copie e cole a seguinte URL no seu navegador: $link</code></p></body></html>";
            
            $crlf = "\r\n";
            $headers = array('From' => $sender, 'Return-Path' => $sender, 'Subject' => $subject, 'Content-Type'  => 'text/html; charset=UTF-8');
            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );
            
            // Creating the Mime message
            /*$mime = new Mail_mime($crlf);
            
            // Setting the body of the email
            $mime->setTXTBody($text);
            $mime->setHTMLBody($html);
            
            $body = $mime->get($mime_params);
            $headers = $mime->headers($headers);
            
            // Sending the email
            $mail =& Mail::factory('mail');
            $mail->send($recipient, $headers, $body);*/

            $to = "$email";
            //             $html = "Hello world!";
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: noreply@$server" . "\r\n";
             // . "CC: somebodyelse@example.com";

            return mail($to,$subject,$html,$headers);
        }
        return false;
    }
}