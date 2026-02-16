-- obtenir la quantité totale obtenue depuis le début, pour chaque besoin possible

CREATE OR REPLACE VIEW v_qte_dons_obtenus AS 
    SELECT 
        b.id AS id_besoin,
        b.nom AS nom_besoin,
        SUM(dd.quantite) AS quantite
    FROM s3_don d
    JOIN s3_don_details dd 
        ON d.id = dd.id_don
    JOIN s3_besoin b
        ON b.id = dd.id_besoin
    GROUP BY b.id, b.nom;

CREATE OR REPLACE VIEW v_qte_besoins_villes AS 
    SELECT 
        b.nom, 
        SUM(bvd.quantite) as total
    FROM s3_besoin_ville_details bvd
    JOIN s3_besoin b 
        ON bvd.id_besoin = b.id
    GROUP BY b.id, b.nom;

-- Vue pour le dispatch : liste toutes les demandes triées par date
CREATE OR REPLACE VIEW v_dispatch_demandes AS
    SELECT 
        bv.id AS id_besoin_ville,
        bv.date_besoin,
        v.id AS id_ville,
        v.nom AS ville_nom,
        b.id AS id_besoin,
        b.nom AS besoin_nom,
        bvd.quantite AS quantite_demandee,
        ROW_NUMBER() OVER (PARTITION BY b.id ORDER BY bv.date_besoin) AS ordre_demande
    FROM s3_besoin_ville bv
    JOIN s3_ville v ON bv.id_ville = v.id
    JOIN s3_besoin_ville_details bvd ON bvd.id_besoin_ville = bv.id
    JOIN s3_besoin b ON bvd.id_besoin = b.id
    ORDER BY b.nom, bv.date_besoin; 