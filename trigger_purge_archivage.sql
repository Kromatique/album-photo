-- Activer le planificateur d'événements (Event Scheduler) sur le serveur MySQL
SET GLOBAL event_scheduler = ON;

DELIMITER $$

-- Supprimer l'événement s'il existe déjà pour pouvoir le recréer
DROP EVENT IF EXISTS `purge_archivage_automatique`$$

-- Créer un événement (souvent appelé à tort trigger temporel) qui s'exécute tous les jours
CREATE EVENT `purge_archivage_automatique`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Suppression des commentaires archivés depuis plus de 15 jours
    DELETE FROM commentaire 
    WHERE statut_suppression = 'ARCHIVE' 
    AND date_archivage < DATE_SUB(NOW(), INTERVAL 15 DAY);

    -- Suppression des photos archivées depuis plus de 15 jours
    DELETE FROM photo 
    WHERE statut_suppression = 'ARCHIVE' 
    AND date_archivage < DATE_SUB(NOW(), INTERVAL 15 DAY);

END$$

DELIMITER ;
