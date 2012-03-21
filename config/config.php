<?php

\Gesdon\Core\Config::add(array(
  'model_dir'       => \Gesdon\Core\Config::get('lib_dir').DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.'classes',
  'pdf_dir'         => \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'pdf',
  'next_num_fiscal' => 1,
  'img_signature'   => \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'img_pdf'.DIRECTORY_SEPARATOR.'signature.jpg',
  'img_logo'        => \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'img_pdf'.DIRECTORY_SEPARATOR.'licence.jpg',
  'img_license'     => \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'img_pdf'.DIRECTORY_SEPARATOR.'logo_framasoft_HD.jpg',
  'smtp'            => false,
  'smtp_server'     => null,
  'smtp_port'       => null,
  'smtp_secure'     => null,
  'smtp_user'       => null,
  'smtp_pass'       => null,
  'mail_bcc'        => null,
  'mail_from'       => 'contact@example.com',
  'mail_attente'    => 20,
  
  'mail_message_recurrent' => <<<EOF
﻿Bonjour %%prenom%%, 

Vous avez fait des dons récurrents pour un montant total de %%montant%% € à Framasoft (durant l'année %%date_don%%). 

Nous vous en remercions chaleureusement. 

Votre participation contribue d'abord à nous aider à maintenir, consolider et adapter le réseau de sites existants : l'annuaire <http://www.framasoft.net>, le portail d'applications libres portables <http://www.framakey.org>, le projet de livres libres <http://www.framabook.org>, le blog d'information autour de la culture libre <http://www.framablog.org>, le forum de la communauté Framasoft <http://forum.framasoft.org>, sans compter les autres projets gravitant autour de ce réseau <http://fr.wikipedia.org/wiki/Framasoft>. 

Mais elle est aussi un apport essentiel qui devrait nous permettre de proposer de nouveaux projets autour de cette "culture libre", qui nous parait aujourd'hui une piste intéressante pour remettre en relation directe les créateurs (de logiciels, mais aussi de contenus) avec des utilisateurs mieux informés, plus actifs, plus libres... 

Nous vous rappelons aussi que votre don ouvre droit à une réduction d'impôts (cf. <http://soutenir.framasoft.org/defiscalisation> ). Nous vous joignons donc en pièce jointe votre reçu fiscal. Pour en bénéficier, il vous suffira de reporter le montant de votre don sur votre déclaration d'impôts* et d'y joindre votre reçu (ou de le conserver précieusement si vous payez vos impôts en ligne). 

Encore une fois, merci beaucoup pour votre soutien. 

La route est longue, mais la voie est libre ! 

Librement, L'équipe Framasoft. 

* : Attention, les dons effectués à compter du 1er janvier 2012 seront à déclarer sur votre déclaration d'impôts 2012 que vous recevrez... en 2013 ! Pour plus d'informations, rendez-vous sur la page <http://soutenir.framasoft.org/defiscalisation>. N'hésitez pas à nous contacter si vous avez des questions. 
EOF
,
  'mail_message' => <<<EOF
﻿Bonjour %%prenom%%, 

Le %%date_don%%, vous avez fait un don de %%montant%% € à Framasoft.

Nous vous en remercions chaleureusement. 

Votre participation contribue d'abord à nous aider à maintenir, consolider et adapter le réseau de sites existants : l'annuaire <http://www.framasoft.net>, le portail d'applications libres portables <http://www.framakey.org>, le projet de livres libres <http://www.framabook.org>, le blog d'information autour de la culture libre <http://www.framablog.org>, le forum de la communauté Framasoft <http://forum.framasoft.org>, sans compter les autres projets gravitant autour de ce réseau <http://fr.wikipedia.org/wiki/Framasoft>. 

Mais elle est aussi un apport essentiel qui devrait nous permettre de proposer de nouveaux projets autour de cette "culture libre", qui nous parait aujourd'hui une piste intéressante pour remettre en relation directe les créateurs (de logiciels, mais aussi de contenus) avec des utilisateurs mieux informés, plus actifs, plus libres... 

Nous vous rappelons aussi que votre don ouvre droit à une réduction d'impôts (cf. <http://soutenir.framasoft.org/defiscalisation> ). Nous vous joignons donc en pièce jointe votre reçu fiscal. Pour en bénéficier, il vous suffira de reporter le montant de votre don sur votre déclaration d'impôts* et d'y joindre votre reçu (ou de le conserver précieusement si vous payez vos impôts en ligne). 

Encore une fois, merci beaucoup pour votre soutien. 

La route est longue, mais la voie est libre ! 

Librement, L'équipe Framasoft. 

* : Attention, les dons effectués à compter du 1er janvier 2012 seront à déclarer sur votre déclaration d'impôts 2012 que vous recevrez... en 2013 ! Pour plus d'informations, rendez-vous sur la page <http://soutenir.framasoft.org/defiscalisation>. N'hésitez pas à nous contacter si vous avez des questions. 

PS : certaines configuration de clients e-mails semblent mal supporter l'encodage du reçu PDF. Si votre pièce jointe vous parvenait dans un mauvais format, n'hésitez pas à nous le faire savoir pour que nous puissions vous en faire parvenir une autre.
EOF
,
  'mail_subject_recurrent' => 'Votre don récurrent à Framasoft',
  'mail_subject' => 'Votre don à Framasoft',
));