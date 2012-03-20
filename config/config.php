<?php

\Gesdon\Core\Config::add(array(
  'model_dir'       => \Gesdon\Core\Config::get('lib_dir').DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.'classes',
  'pdf_dir'         => \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'pdf',
  'next_num_fiscal' => 2000,
  'img_signature'   => \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'img_pdf'.DIRECTORY_SEPARATOR.'signature.jpg',
  'img_logo'        => \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'img_pdf'.DIRECTORY_SEPARATOR.'licence.jpg',
  'img_license'     => \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'img_pdf'.DIRECTORY_SEPARATOR.'logo_framasoft_HD.jpg',
));