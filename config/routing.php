<?php

$routing = array(
    // Page d'accueil
    '/' => array(
        'class' => 'Index',
        'name' => 'homepage',
    ),
    
    // Page de don
    '/dons' => array(
        'class' => 'Dons',
        'name' => 'dons'
    ),
    '/dons/{id}' => array(
        'class' => 'Don',
        'name' => 'don',
    ),
    '/dons/add' => array(
        'class' => 'Don',
        'name' => 'don_add'
    ),
    
    // Page des donateurs
    '/donateur/{id}' => array(
        'class' => 'Donateur',
        'name' => 'donateur'
    ),
    
    // Page des tâches
    '/taches' => array(
        'class' => 'Task',
        'name' => 'task',
    ),
    '/taches/recus' => array(
        'class' => 'TaskRecu',
        'name' => 'task_recus'
    ),
    '/taches/cartes' => array(
        'class' => 'TaskCard',
        'name' => 'task_card'
    ),


    // Page d'envoi des reçus
    '/recus/{id}' => array(
        'class' => 'Recu',
        'name' => 'recu',
    ),
);