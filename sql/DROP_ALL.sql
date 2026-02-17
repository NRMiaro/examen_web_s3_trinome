-- =========================================================================
-- SCRIPT DE DROP COMPLET - PROJET BNGRC
-- =========================================================================
-- Ce script supprime toutes les tables, vues et autres objets de base de données
-- du projet de suivi des dons et catastrophes pour le BNGRC.
-- 
-- ATTENTION : Cette opération est IRRÉVERSIBLE !
-- Toutes les données seront perdues définitivement.
-- 
-- Généré automatiquement le 17/02/2026
-- =========================================================================

USE s3_bngrc;

-- =========================================================================
-- 1. SUPPRESSION DES VUES (pas de dépendances FK)
-- =========================================================================
DROP VIEW IF EXISTS v_dispatch_montants;
DROP VIEW IF EXISTS v_dispatch_stats;
DROP VIEW IF EXISTS v_dispatch_validation;
DROP VIEW IF EXISTS v_dispatch_demandes;
DROP VIEW IF EXISTS v_solde_caisse;
DROP VIEW IF EXISTS v_caisse;
DROP VIEW IF EXISTS v_qte_besoins_villes;
DROP VIEW IF EXISTS v_qte_dons_obtenus;

-- =========================================================================
-- 2. SUPPRESSION DES TABLES (ordre des contraintes FK)
-- =========================================================================

-- Tables de mouvement/validation (références vers les tables CRUD)
DROP TABLE IF EXISTS s3_achat;
DROP TABLE IF EXISTS s3_dispatch_validation;

-- Tables détails (références vers les tables principales)
DROP TABLE IF EXISTS s3_don_details;
DROP TABLE IF EXISTS s3_besoin_ville_details;

-- Tables principales avec FK vers d'autres tables
DROP TABLE IF EXISTS s3_don;
DROP TABLE IF EXISTS s3_besoin_ville;
DROP TABLE IF EXISTS s3_besoin;

-- Tables autonomes ou standalone
DROP TABLE IF EXISTS s3_don_financier;
DROP TABLE IF EXISTS s3_type_don;
DROP TABLE IF EXISTS s3_type_besoin;
DROP TABLE IF EXISTS s3_ville;

-- =========================================================================
-- 3. SUPPRESSION DE LA BASE DE DONNÉES (optionnel)
-- =========================================================================
-- Décommentez la ligne suivante pour supprimer complètement la base de données :
-- DROP DATABASE IF EXISTS s3_bngrc;

-- =========================================================================
-- RÉSUMÉ DES OBJETS SUPPRIMÉS :
-- =========================================================================
--
-- VUES SUPPRIMÉES (8) :
--   - v_dispatch_montants       : Montants demandés vs alloués
--   - v_dispatch_stats          : Statistiques globales du dispatch
--   - v_dispatch_validation     : Allocations validées détaillées
--   - v_dispatch_demandes       : Demandes triées par date
--   - v_solde_caisse           : Solde caisse (dons - achats)
--   - v_caisse                 : Dons financiers
--   - v_qte_besoins_villes     : Quantités demandées par besoin
--   - v_qte_dons_obtenus       : Quantités obtenues par don
--
-- TABLES SUPPRIMÉES (11) :
--   - s3_achat                 : Achats effectués avec argent caisse
--   - s3_dispatch_validation   : Validations d'allocations de simulation
--   - s3_don_details           : Détails des dons matériels
--   - s3_besoin_ville_details  : Détails des demandes par ville
--   - s3_don                   : Dons matériels (header)
--   - s3_besoin_ville         : Demandes par ville (header)
--   - s3_besoin               : Catalogue des besoins possibles
--   - s3_don_financier        : Dons d'argent (caisse)
--   - s3_type_don             : Types de dons
--   - s3_type_besoin          : Types de besoins
--   - s3_ville                : Liste des villes
--
-- CONTRAINTES RESPECTÉES :
--   ✓ Suppression des vues avant les tables
--   ✓ Suppression des tables enfants avant parents
--   ✓ Respect de l'ordre des clés étrangères
--
-- =========================================================================