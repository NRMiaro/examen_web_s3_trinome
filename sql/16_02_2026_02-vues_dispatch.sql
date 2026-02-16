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
    GROUP BY b.id, b.nom

CREATE OR REPLACE VIEW 