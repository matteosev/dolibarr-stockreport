<?php
class StockReport
{
    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function sendReport()
    {
        global $conf;
        require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';

        // Extraction des produits dont le stock en RAYON est inférieur au stock désiré
        $sql = "SELECT p.ref, p.label, p.desiredstock, ps.reel, (p.desiredstock - ps.reel) as qty_to_restock ";
        $sql .= "FROM " . MAIN_DB_PREFIX . "product p ";
        $sql .= "JOIN " . MAIN_DB_PREFIX . "product_stock ps ON p.rowid = ps.fk_product ";
        $sql .= "JOIN " . MAIN_DB_PREFIX . "entrepot e ON ps.fk_entrepot = e.rowid ";
        $sql .= "WHERE e.ref = 'RAYON' ";
        $sql .= "AND ps.reel < p.desiredstock ";
        $sql .= "AND p.desiredstock IS NOT NULL";

        $resql = $this->db->query($sql);

        if (!$resql) {
            return -1;
        }

        $html = "<h3>Rapport de réapprovisionnement pour le RAYON</h3>";
        $html .= "<table border='1' cellpadding='5' cellspacing='0'>";
        $html .= "<tr><th>Référence</th><th>Produit</th><th>Stock actuel</th><th>Objectif</th><th>Quantité à placer en rayon</th></tr>";

        while ($obj = $this->db->fetch_object($resql)) {
            $html .= "<tr>";
            $html .= "<td>" . $obj->ref . "</td>";
            $html .= "<td>" . $obj->label . "</td>";
            $html .= "<td>" . $obj->reel . "</td>";
            $html .= "<td>" . $obj->desiredstock . "</td>";
            $html .= "<td><strong>" . $obj->qty_to_restock . "</strong></td>";
            $html .= "</tr>";
        }
        $html .= "</table>";

        $subject = "Rapport quotidien de mise en rayon";
        $from = !empty($conf->global->MAIN_INFO_SOCIETE_MAIL) ? $conf->global->MAIN_INFO_SOCIETE_MAIL : "noreply@epicerie.com";
        $to = "TO_REPLACE"; // I didn't put my personal email address for obvious security reasons

        $mailfile = new CMailFile($subject, $to, $from, $html, array(), array(), array(), '', '', 0, 1);
        $result = $mailfile->sendfile();

        return $result ? 0 : -1;
    }
}