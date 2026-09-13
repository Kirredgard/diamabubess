<?php
// Diama Bu Bess — endpoint des formulaires Contact et Adhésion
// À déposer dans /www/contact.php sur l'hébergement OVH.

header('Content-Type: application/json; charset=utf-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = [
    'https://diamabubess.sn',
    'https://www.diamabubess.sn'
];
if (in_array($origin, $allowed, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Méthode non autorisée.']);
    exit;
}

$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
if (strpos($contentType, 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    // Les formulaires du site utilisent application/x-www-form-urlencoded :
    // cela évite le pré-vol CORS (OPTIONS), notamment sur Safari/iPhone.
    $data = $_POST;
}
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Données invalides.']);
    exit;
}

// Piège anti-spam invisible.
if (!empty($data['website'])) {
    echo json_encode(['success'=>true]);
    exit;
}

$clean = static function($value, $max = 5000) {
    $value = trim((string)$value);
    $value = str_replace(["\r", "\n"], ' ', $value);
    return substr($value, 0, $max);
};

$type = ($data['type'] ?? 'contact') === 'adhesion' ? 'adhesion' : 'contact';
$nom = $clean($data['nom'] ?? '', 120);
$prenom = $clean($data['prenom'] ?? '', 120);
$telephone = $clean($data['telephone'] ?? '', 50);
$email = trim((string)($data['email'] ?? ''));
$message = trim((string)($data['message'] ?? ''));
$message = substr($message, 0, 8000);

if ($type === 'adhesion') {
    $residence = $clean($data['residence'] ?? '', 200);
    $residenceType = $clean($data['residence_type'] ?? '', 50);
    $communeMilitantisme = $clean($data['commune_militantisme'] ?? '', 150);
    $profession = $clean($data['profession'] ?? '', 150);
    $modeCarte = $clean($data['mode_carte'] ?? '', 100);

    if ($nom === '' || $prenom === '' || $telephone === '' || $residence === '') {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'Prénom, nom, téléphone et lieu de résidence sont obligatoires.']);
        exit;
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'Adresse e-mail invalide.']);
        exit;
    }

    $fullName = trim($prenom . ' ' . $nom);
    $subject = 'Nouvelle adhésion — Diama Bu Bess';
    $body = "NOUVELLE DEMANDE D'ADHÉSION — DIAMA BU BESS\n\n";
    $body .= "Prénom : {$prenom}\n";
    $body .= "Nom : {$nom}\n";
    $body .= "Téléphone : {$telephone}\n";
    $body .= "E-mail : " . ($email !== '' ? $email : 'Non renseigné') . "\n";
    $body .= "Lieu de résidence : {$residence}\n";
    $body .= "Type de résidence : {$residenceType}\n";
    $body .= "Commune où la personne milite : " . ($communeMilitantisme !== '' ? $communeMilitantisme : 'Même lieu que la résidence') . "\n";
    $body .= "Profession / activité : " . ($profession !== '' ? $profession : 'Non renseignée') . "\n";
    $body .= "Réception de la carte : {$modeCarte}\n\n";
    $body .= "Message / motivation :\n" . ($message !== '' ? $message : 'Non renseigné') . "\n";
    $body .= "\nNom complet : {$fullName}\n";

    $headers = "From: Diama Bu Bess <contact@diamabubess.sn>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= $email !== '' ? "Reply-To: {$email}\r\n" : "Reply-To: contact@diamabubess.sn\r\n";

    $adminTo = 'contact@diamabubess.sn';
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $sent = mail($adminTo, $encodedSubject, $body, $headers);
    if (!$sent) {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>'Le serveur n’a pas pu envoyer la demande d’adhésion.']);
        exit;
    }

    // Accusé de réception automatique au demandeur, si une adresse e-mail valide a été fournie.
    $confirmationSent = false;
    if ($email !== '') {
        $replySubject = 'Votre demande d’adhésion — Diama Bu Bess';
        $replyBody = "Bonjour {$prenom},\n\n";
        $replyBody .= "Nous vous remercions pour votre demande d’adhésion à Diama Bu Bess.\n\n";
        $replyBody .= "Votre demande a bien été reçue par notre équipe. Nous allons l’examiner et vous recontacter prochainement avec les prochaines informations concernant votre adhésion.\n\n";
        $replyBody .= "À bientôt,\n";
        $replyBody .= "L’équipe Diama Bu Bess\n";
        $replyBody .= "contact@diamabubess.sn\n";

        $replyHeaders = "From: Diama Bu Bess <contact@diamabubess.sn>\r\n";
        $replyHeaders .= "Reply-To: contact@diamabubess.sn\r\n";
        $replyHeaders .= "MIME-Version: 1.0\r\n";
        $replyHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $confirmationSent = mail($email, '=?UTF-8?B?' . base64_encode($replySubject) . '?=', $replyBody, $replyHeaders);
    }

    echo json_encode(['success'=>true, 'confirmation_sent'=>$confirmationSent]);
    exit;
}

// Formulaire Contact existant.
$sujet = $clean($data['sujet'] ?? 'Question générale', 120);
if ($nom === '' || $telephone === '' || $message === '') {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Nom, téléphone et message sont obligatoires.']);
    exit;
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Adresse e-mail invalide.']);
    exit;
}

$to = 'contact@diamabubess.sn';
$subject = 'Nouveau message — Diama Bu Bess — ' . $sujet;
$body = "Nouveau message reçu depuis le site diamabubess.sn\n\n";
$body .= "Nom : {$nom}\n";
$body .= "Téléphone : {$telephone}\n";
$body .= "E-mail : " . ($email !== '' ? $email : 'Non renseigné') . "\n";
$body .= "Sujet : {$sujet}\n\n";
$body .= "Message :\n{$message}\n";

$headers = "From: Diama Bu Bess <contact@diamabubess.sn>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= $email !== '' ? "Reply-To: {$email}\r\n" : "Reply-To: contact@diamabubess.sn\r\n";

$sent = mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
if (!$sent) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Le serveur n’a pas pu envoyer le message.']);
    exit;
}

// Accusé de réception automatique au visiteur, si une adresse e-mail valide a été fournie.
$confirmationSent = false;
if ($email !== '') {
    $replySubject = 'Nous avons bien reçu votre message — Diama Bu Bess';
    $replyBody = "Bonjour {$nom},\n\n";
    $replyBody .= "Nous vous remercions d’avoir contacté Diama Bu Bess.\n\n";
    $replyBody .= "Votre message a bien été reçu par notre équipe. Nous vous recontacterons dans les meilleurs délais.\n\n";
    $replyBody .= "À bientôt,\n";
    $replyBody .= "L’équipe Diama Bu Bess\n";
    $replyBody .= "contact@diamabubess.sn\n";

    $replyHeaders = "From: Diama Bu Bess <contact@diamabubess.sn>\r\n";
    $replyHeaders .= "Reply-To: contact@diamabubess.sn\r\n";
    $replyHeaders .= "MIME-Version: 1.0\r\n";
    $replyHeaders .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $confirmationSent = mail($email, '=?UTF-8?B?' . base64_encode($replySubject) . '?=', $replyBody, $replyHeaders);
}

echo json_encode(['success'=>true, 'confirmation_sent'=>$confirmationSent]);
