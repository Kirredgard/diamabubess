<?php
// Diama Bu Bess — endpoint Contact + Adhésion — OVH
header('Content-Type: application/json; charset=utf-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = ['https://diamabubess.sn','https://www.diamabubess.sn'];
if (in_array($origin, $allowed, true)) {
    header('Access-Control-Allow-Origin: '.$origin);
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
$wantsJson = strpos($accept, 'application/json') !== false;

function answer($payload, $status = 200) {
    global $wantsJson;
    http_response_code($status);
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
    // Réponse pour le formulaire HTML natif chargé dans une iframe cachée.
    $success = !empty($payload['success']);
    $message = htmlspecialchars((string)($payload['message'] ?? ($success ? 'Votre demande a bien été reçue.' : 'Une erreur est survenue.')), ENT_QUOTES, 'UTF-8');
    $confirmation = !empty($payload['confirmation_sent']) ? 'true' : 'false';
    $ok = $success ? 'true' : 'false';
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Diama Bu Bess</title></head><body><script>';
    echo 'window.parent.postMessage({source:"diama-contact",success:'.$ok.',confirmation_sent:'.$confirmation.',message:'.json_encode($message, JSON_UNESCAPED_UNICODE).'},"*");';
    echo '</script></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    answer(['success'=>false,'message'=>'Méthode non autorisée.'], 405);
}

$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
$data = strpos($contentType, 'application/json') !== false
    ? json_decode(file_get_contents('php://input'), true)
    : $_POST;
if (!is_array($data)) answer(['success'=>false,'message'=>'Données invalides.'], 400);
if (!empty($data['website'])) answer(['success'=>true], 200);

$clean = static function($value,$max=5000){
    $v=trim((string)$value);
    $v=str_replace(["\r","\n"],' ',$v);
    return substr($v,0,$max);
};
$type = ($data['type'] ?? 'contact') === 'adhesion' ? 'adhesion' : 'contact';
$nom=$clean($data['nom']??'',120);
$prenom=$clean($data['prenom']??'',120);
$telephone=$clean($data['telephone']??'',50);
$email=trim((string)($data['email']??''));
$message=substr(trim((string)($data['message']??'')),0,8000);
if ($email!=='' && !filter_var($email,FILTER_VALIDATE_EMAIL)) {
    answer(['success'=>false,'message'=>'Adresse e-mail invalide.'], 422);
}

$admin='contact@diamabubess.sn';
$from="From: Diama Bu Bess <contact@diamabubess.sn>\r\n";
$from .= "MIME-Version: 1.0\r\n";
$from .= "Content-Type: text/plain; charset=UTF-8\r\n";
$from .= "X-Mailer: PHP/".phpversion()."\r\n";

if ($type==='adhesion') {
    $residence=$clean($data['residence']??'',200);
    $residenceType=$clean($data['residence_type']??'',50);
    $commune=$clean($data['commune_militantisme']??'',150);
    $profession=$clean($data['profession']??'',150);
    $modeCarte=$clean($data['mode_carte']??'',100);
    if($nom===''||$prenom===''||$telephone===''||$residence===''){
        answer(['success'=>false,'message'=>'Prénom, nom, téléphone et lieu de résidence sont obligatoires.'],422);
    }
    $subject='Nouvelle adhésion — Diama Bu Bess';
    $body="NOUVELLE DEMANDE D'ADHÉSION — DIAMA BU BESS\n\nPrénom : $prenom\nNom : $nom\nTéléphone : $telephone\nE-mail : ".($email?:'Non renseigné')."\nLieu de résidence : $residence\nType de résidence : $residenceType\nCommune où la personne milite : ".($commune?:'Même lieu que la résidence')."\nProfession / activité : ".($profession?:'Non renseignée')."\nRéception de la carte : $modeCarte\n\nMessage / motivation :\n".($message?:'Non renseigné')."\n";
    $h=$from.($email!==''?"Reply-To: $email\r\n":"Reply-To: $admin\r\n");
    $sent=mail($admin,'=?UTF-8?B?'.base64_encode($subject).'?=',$body,$h);
    if(!$sent) answer(['success'=>false,'message'=>'Le serveur n’a pas pu envoyer la demande d’adhésion.'],500);
    $confirmationSent=false;
    if($email!==''){
        $reply="Bonjour $prenom,\n\nNous vous remercions pour votre demande d’adhésion à Diama Bu Bess.\n\nVotre demande a bien été reçue par notre équipe. Nous allons l’examiner et vous recontacter prochainement.\n\nÀ bientôt,\nL’équipe Diama Bu Bess\ncontact@diamabubess.sn\n";
        $rh=$from."Reply-To: $admin\r\n";
        $confirmationSent=mail($email,'=?UTF-8?B?'.base64_encode('Votre demande d’adhésion — Diama Bu Bess').'?=',$reply,$rh);
    }
    answer(['success'=>true,'confirmation_sent'=>$confirmationSent,'message'=>'Votre demande d’adhésion a bien été reçue.']);
}

if($nom===''||$telephone===''||$message===''){
    answer(['success'=>false,'message'=>'Nom, téléphone et message sont obligatoires.'],422);
}
$sujet=$clean($data['sujet']??'Question générale',120);
$subject='Nouveau message — Diama Bu Bess — '.$sujet;
$body="Nouveau message reçu depuis le site diamabubess.sn\n\nNom : $nom\nTéléphone : $telephone\nE-mail : ".($email?:'Non renseigné')."\nSujet : $sujet\n\nMessage :\n$message\n";
$h=$from.($email!==''?"Reply-To: $email\r\n":"Reply-To: $admin\r\n");
$sent=mail($admin,'=?UTF-8?B?'.base64_encode($subject).'?=',$body,$h);
if(!$sent) answer(['success'=>false,'message'=>'Le serveur n’a pas pu envoyer le message.'],500);
$confirmationSent=false;
if($email!==''){
    $reply="Bonjour $nom,\n\nNous vous remercions d’avoir contacté Diama Bu Bess.\n\nVotre message a bien été reçu par notre équipe. Nous vous recontacterons dans les meilleurs délais.\n\nÀ bientôt,\nL’équipe Diama Bu Bess\ncontact@diamabubess.sn\n";
    $rh=$from."Reply-To: $admin\r\n";
    $confirmationSent=mail($email,'=?UTF-8?B?'.base64_encode('Nous avons bien reçu votre message — Diama Bu Bess').'?=',$reply,$rh);
}
answer(['success'=>true,'confirmation_sent'=>$confirmationSent,'message'=>'Votre message a bien été reçu.']);
?>
