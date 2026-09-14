<?php
// Diama Bu Bess — endpoint Contact + Adhésion — OVH
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = ['https://diamabubess.sn','https://www.diamabubess.sn'];
if (in_array($origin, $allowed, true)) {
    header('Access-Control-Allow-Origin: '.$origin);
    header('Vary: Origin');
}
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$returnTo = 'https://diamabubess.sn/';

function clean($value, $max=5000) {
    $v = trim((string)$value);
    $v = str_replace(["\r","\n"], ' ', $v);
    return substr($v, 0, $max);
}
function redirect_result($base, $success, $extra=[]) {
    $url = $base . (strpos($base, '?') === false ? '?' : '&') . 'sent=' . ($success ? '1' : '0');
    if ($success) {
        foreach ($extra as $k=>$v) {
            if ($v !== '') $url .= '&' . rawurlencode($k) . '=' . rawurlencode($v);
        }
    } elseif (!empty($extra['error'])) {
        $url .= '&error=' . rawurlencode($extra['error']);
    }
    header('Location: '.$url, true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>false,'message'=>'Méthode non autorisée.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = $_POST;
if (!is_array($data)) {
    http_response_code(400);
    echo 'Données invalides.';
    exit;
}

$requestedReturn = trim((string)($data['return_to'] ?? ''));
if ($requestedReturn === 'https://diamabubess.sn/contact/' || $requestedReturn === 'https://www.diamabubess.sn/contact/') {
    $returnTo = $requestedReturn;
} elseif ($requestedReturn === 'https://diamabubess.sn/adhesion/' || $requestedReturn === 'https://www.diamabubess.sn/adhesion/') {
    $returnTo = $requestedReturn;
}

if (!empty($data['website'])) redirect_result($returnTo, true);

$type = ($data['type'] ?? 'contact') === 'adhesion' ? 'adhesion' : 'contact';
$nom = clean($data['nom'] ?? '', 120);
$prenom = clean($data['prenom'] ?? '', 120);
$telephone = clean($data['telephone'] ?? '', 50);
$email = trim((string)($data['email'] ?? ''));
$message = substr(trim((string)($data['message'] ?? '')), 0, 8000);

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_result($returnTo, false, ['error'=>'Adresse e-mail invalide.']);
}

$admin = 'contact@diamabubess.sn';
$from  = "From: Diama Bu Bess <contact@diamabubess.sn>\r\n";
$from .= "MIME-Version: 1.0\r\n";
$from .= "Content-Type: text/plain; charset=UTF-8\r\n";
$from .= "Content-Transfer-Encoding: 8bit\r\n";
$from .= "Date: ".date(DATE_RFC2822)."\r\n";
$from .= "X-Mailer: PHP/".phpversion()."\r\n";
$envelope = '-f contact@diamabubess.sn';

if ($type === 'adhesion') {
    $residence = clean($data['residence'] ?? '', 200);
    $residenceType = clean($data['residence_type'] ?? '', 50);
    $commune = clean($data['commune_militantisme'] ?? '', 150);
    $profession = clean($data['profession'] ?? '', 150);
    $modeCarte = clean($data['mode_carte'] ?? '', 100);

    if ($nom === '' || $prenom === '' || $telephone === '' || $residence === '') {
        redirect_result($returnTo, false, ['error'=>'Prénom, nom, téléphone et lieu de résidence sont obligatoires.']);
    }

    $subject = 'Nouvelle adhésion — Diama Bu Bess';
    $body = "NOUVELLE DEMANDE D'ADHÉSION — DIAMA BU BESS\n\n".
        "Prénom : $prenom\nNom : $nom\nTéléphone : $telephone\nE-mail : ".($email ?: 'Non renseigné').
        "\nLieu de résidence : $residence\nType de résidence : $residenceType\nCommune où la personne milite : ".($commune ?: 'Même lieu que la résidence').
        "\nProfession / activité : ".($profession ?: 'Non renseignée')."\nRéception de la carte : $modeCarte\n\nMessage / motivation :\n".($message ?: 'Non renseigné')."\n";
    $h = $from . ($email !== '' ? "Reply-To: $email\r\n" : "Reply-To: $admin\r\n");
    $sent = mail($admin, '=?UTF-8?B?'.base64_encode($subject).'?=', $body, $h, $envelope);
    if (!$sent) redirect_result($returnTo, false, ['error'=>'Le serveur n’a pas pu envoyer la demande d’adhésion.']);

    $confirmationSent = false;
    if ($email !== '') {
        $reply = "Bonjour $prenom,\n\nNous vous remercions pour votre demande d’adhésion à Diama Bu Bess.\n\nVotre demande a bien été reçue par notre équipe. Nous allons l’examiner et vous recontacter prochainement.\n\nÀ bientôt,\nL’équipe Diama Bu Bess\ncontact@diamabubess.sn\n";
        $rh = $from . "Reply-To: $admin\r\n";
        $confirmationSent = mail($email, '=?UTF-8?B?'.base64_encode('Votre demande d’adhésion — Diama Bu Bess').'?=', $reply, $rh, $envelope);
    }
    redirect_result($returnTo, true, ['email'=>$email]);
}

if ($nom === '' || $telephone === '' || $message === '') {
    redirect_result($returnTo, false, ['error'=>'Nom, téléphone et message sont obligatoires.']);
}

$sujet = clean($data['sujet'] ?? 'Question générale', 120);
$subject = 'Nouveau message — Diama Bu Bess — '.$sujet;
$body = "Nouveau message reçu depuis le site diamabubess.sn\n\nNom : $nom\nTéléphone : $telephone\nE-mail : ".($email ?: 'Non renseigné')."\nSujet : $sujet\n\nMessage :\n$message\n";
$h = $from . ($email !== '' ? "Reply-To: $email\r\n" : "Reply-To: $admin\r\n");
$sent = mail($admin, '=?UTF-8?B?'.base64_encode($subject).'?=', $body, $h, $envelope);
if (!$sent) redirect_result($returnTo, false, ['error'=>'Le serveur n’a pas pu envoyer le message.']);

if ($email !== '') {
    $reply = "Bonjour $nom,\n\nNous vous remercions d’avoir contacté Diama Bu Bess.\n\nVotre message a bien été reçu par notre équipe. Nous vous recontacterons dans les meilleurs délais.\n\nÀ bientôt,\nL’équipe Diama Bu Bess\ncontact@diamabubess.sn\n";
    $rh = $from . "Reply-To: $admin\r\n";
    mail($email, '=?UTF-8?B?'.base64_encode('Nous avons bien reçu votre message — Diama Bu Bess').'?=', $reply, $rh, $envelope);
}
redirect_result($returnTo, true, ['name'=>$nom]);
?>
