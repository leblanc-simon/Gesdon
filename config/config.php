<?php

\Gesdon\Core\Config::add(array(
  'model_dir'       => \Gesdon\Core\Config::get('lib_dir').DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.'classes',
  'pdf_dir'         => \Gesdon\Core\Config::get('data_dir').DIRECTORY_SEPARATOR.'pdf',
  'next_num_fiscal' => 2000,
));