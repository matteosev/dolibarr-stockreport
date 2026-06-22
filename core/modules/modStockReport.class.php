<?php
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';

class modStockReport extends DolibarrModules
{
    public function __construct($db)
    {
        global $conf;
        $this->db = $db;
        $this->numero = 105000;
        $this->rights_class = 'stockreport';
        $this->family = 'products';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = "Génération et envoi du rapport de mise en rayon.";
        $this->version = '1.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'technic';
        $this->module_parts = array();
        $this->dirs = array('/stockreport');

        // Déclaration de la tâche planifiée
        $this->cronjobs = array(
            0 => array(
                'label' => 'RapportMiseEnRayon',
                'jobtype' => 'method',
                'class' => '/stockreport/class/stockreport.class.php',
                'objectname' => 'StockReport',
                'method' => 'sendReport',
                'parameters' => '',
                'comment' => 'Envoi quotidien du rapport de réapprovisionnement',
                'frequency' => 1,
                'unitfrequency' => 3600*24, // Exécution toutes les 24h
                'status' => 1
            )
        );
    }
}