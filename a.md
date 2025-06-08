# Proiect PAW: Magazin Virtual (2024-2025)

Acest proiect reprezintă o aplicație web de tip magazin virtual, dezvoltată în PHP și MySQL, conform cerințelor cursului de Programarea Aplicațiilor Web. Aplicația permite utilizatorilor să vizualizeze produse, iar administratorilor să gestioneze inventarul și caracteristicile acestora.






## Cuprins

- [Proiect PAW: Magazin Virtual (2024-2025)](#proiect-paw-magazin-virtual-2024-2025)
  - [Cuprins](#cuprins)
  - [1. Descriere Generală](#1-descriere-generală)
  - [2. Funcționalități](#2-funcționalități)
    - [Partea Publică (Client)](#partea-publică-client)
    - [Partea de Administrare (Admin)](#partea-de-administrare-admin)
  - [3. Tehnologii Folosite](#3-tehnologii-folosite)
  - [4. Structura Proiectului](#4-structura-proiectului)
  - [5. Schema Bazei de Date (Conceptuală)](#5-schema-bazei-de-date-conceptuală)
  - [6. Schema Logică a Aplicației](#6-schema-logică-a-aplicației)


## 1. Descriere Generală

Aplicația simulează un magazin online unde produsele sunt prezentate cu detalii, imagini și caracteristici. Există o interfață pentru clienți și un panou de administrare protejat prin parolă.

*   **Partea Client (tema b - parțial):** Permite navigarea, vizualizarea listei de produse, vizualizarea detaliilor unui produs (inclusiv imagini multiple și caracteristici), căutare și sortare produse, înregistrare și autentificare utilizatori.
*   **Partea Admin (tema a & b - parțial):** Permite gestionarea produselor (adăugare, editare, ștergere), încărcarea și redimensionarea automată a imaginilor pentru produse, gestionarea caracteristicilor produselor și asignarea acestora.

## 2. Funcționalități

### Partea Publică (Client)

*   **Homepage (`public/index.php`):**
    *   Afișează un banner principal (hero section).
    *   O scurtă prezentare a magazinului.
    *   O secțiune cu "Produse Recomandate/Noi" (ultimele 4 adăugate).
    *   Un formular de abonare la newsletter (doar UI, fără funcționalitate backend).
*   **Listă Produse (`public/products_list.php`):**
    *   Afișează toate produsele disponibile, paginate.
    *   Permite sortarea produselor după: cele mai noi, preț (crescător/descrescător), nume (A-Z, Z-A).
    *   Permite căutarea produselor după termeni cheie în nume și descrieri.
    *   Afișează imaginea principală (thumbnail), numele, prețul și o scurtă descriere pentru fiecare produs.
    *   Link către pagina de detalii a produsului.
    *   Paginare avansată cu link-uri "Anterior", "Următor" și numere de pagină.
*   **Detalii Produs (`public/product_detail.php`):**
    *   Afișează numele complet, descrierea scurtă și detaliată a produsului.
    *   Afișează prețul.
    *   Afișează imaginea principală a produsului.
    *   Afișează o galerie de imagini secundare (thumbnails). Click pe un thumbnail actualizează imaginea principală (funcționalitate JavaScript simplă).
    *   Listează caracteristicile produsului și valorile acestora (ex: Culoare: Roșu, Material: Bumbac).
*   **Înregistrare Utilizator (`public/register.php` & `public/process_register.php`):**
    *   Formular pentru crearea unui cont nou cu nume de utilizator, email și parolă.
    *   Validări pentru câmpuri (obligatorii, format email, lungime parolă, potrivire parole).
    *   Verificare unicitate nume utilizator și email în baza de date.
    *   Parola este stocată în baza de date folosind hashing (`password_hash`).
*   **Autentificare Utilizator (`public/login.php` & `public/process_login.php`):**
    *   Formular pentru autentificare cu nume utilizator/email și parolă.
    *   Verifică credențialele cu baza de date (`password_verify`).
    *   Utilizează sesiuni PHP pentru a menține starea de autentificare.
*   **Deconectare Utilizator (`public/logout.php`):**
    *   Încheie sesiunea utilizatorului și îl redirecționează.
*   **Navigație și Antet/Subsol (`includes/header.php`, `includes/footer.php`):**
    *   Meniu de navigare consistent pe toate paginile publice.
    *   Link către panoul de autentificare admin.
    *   Afișare mesaje flash (succes, eroare, informare) pentru feedback către utilizator.

### Partea de Administrare (Admin)

*   **Autentificare Admin (`admin/login.php` - implicit, procesare în `admin/process_admin_login.php` - *nefurnizat, dar necesar*):**
    *   Formular securizat pentru login administratori.
    *   Utilizează sesiuni PHP.
*   **Panou Principal Admin (`admin/index.php`):**
    *   Pagină de встреча (dashboard) după autentificare.
    *   Meniu de navigare specific zonei de admin.
*   **Gestionare Produse:**
    *   **Listare Produse (`admin/list_products_admin.php` - *nefurnizat, dar menționat*):**
        *   Afișează o listă tabelară a tuturor produselor.
        *   Opțiuni pentru editare și ștergere pentru fiecare produs.
        *   Link pentru adăugarea unui nou produs.
    *   **Adăugare Produs (`admin/add_product.php` - *nefurnizat, dar menționat & descris în tema 'a'*):**
        *   Formular pentru introducerea detaliilor unui nou produs: nume, descriere scurtă/detaliată, preț.
        *   Permite încărcarea unei fotografii principale și a uneia sau mai multor fotografii secundare.
        *   Fotografiile încărcate sunt redimensionate automat: o versiune pentru afișare mare și un thumbnail. Redimensionarea se face cu funcția `resizeImage()` din `includes/functions.php`.
        *   Imaginile sunt salvate în directoare dedicate (`uploads/products/` și `uploads/products_thumbnails/`).
    *   **Editare Produs (`admin/edit_product.php` - *nefurnizat, dar menționat*):**
        *   Formular pre-completat cu datele produsului existent.
        *   Permite modificarea tuturor detaliilor, inclusiv gestionarea (adăugare/ștergere) imaginilor.
*   **Gestionare Caracteristici:**
    *   **Administrare Tipuri de Caracteristici (`admin/manage_characteristics.php` - *nefurnizat, dar menționat & descris în tema 'b'*):**
        *   Permite adăugarea, editarea, ștergerea tipurilor de caracteristici (ex: "Culoare", "Dimensiune", "Material").
    *   **Asignare Caracteristici la Produse (`admin/assign_characteristics.php` - *nefurnizat, dar menționat & descris în tema 'b'*):**
        *   Interfață pentru a selecta un produs și a-i asocia caracteristici definite anterior, specificând valoarea pentru fiecare (ex: pentru produsul "Tricou Model X", caracteristica "Culoare" are valoarea "Albastru").
*   **Deconectare Admin (`admin/logout.php` - *nefurnizat, dar menționat*):**
    *   Încheie sesiunea adminului.
*   **Antet/Subsol Admin (`includes/header_admin.php`, `includes/footer_admin.php`):**
    *   Structură specifică pentru panoul de administrare.
    *   Navigație între modulele de administrare.

## 3. Tehnologii Folosite

*   **Backend:** PHP (>= 7.4 recomandat)
*   **Bază de Date:** MySQL (cu PDO pentru interacțiune)
*   **Frontend:** HTML5, CSS3, JavaScript (vanilla)
*   **Server Web:** Apache sau Nginx (configurat pentru PHP)
*   **Manipulare Imagini:** GD Library (extensie PHP) pentru funcția `resizeImage`.

## 4. Structura Proiectului


## 5. Schema Bazei de Date (Conceptuală)

**Tabela `users`** (pentru clienți)
*   `id` (INT, PK, AI)
*   `username` (VARCHAR, UNIQUE)
*   `email` (VARCHAR, UNIQUE)
*   `password_hash` (VARCHAR)
*   `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

**Tabela `admins`** (pentru administratori - *necesară, dar nu în codul furnizat*)
*   `id` (INT, PK, AI)
*   `username` (VARCHAR, UNIQUE)
*   `password_hash` (VARCHAR)
*   `email` (VARCHAR, UNIQUE, NULLABLE)
*   `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

**Tabela `products`**
*   `id` (INT, PK, AI)
*   `name` (VARCHAR)
*   `short_description` (TEXT)
*   `detailed_description` (TEXT, NULLABLE)
*   `price` (DECIMAL)
*   `main_photo_filename` (VARCHAR, NULLABLE) - Numele fișierului imaginii principale
*   `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
*   `updated_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

**Tabela `product_photos`** (pentru imagini secundare)
*   `id` (INT, PK, AI)
*   `product_id` (INT, FK to `products.id`)
*   `filename` (VARCHAR) - Numele fișierului imaginii
*   `is_main` (BOOLEAN, DEFAULT 0) - *Ar putea fi eliminat dacă `main_photo_filename` e în `products`*
*   `uploaded_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

**Tabela `characteristics`** (tipurile de caracteristici)
*   `id` (INT, PK, AI)
*   `name` (VARCHAR, UNIQUE) - Ex: "Culoare", "Material", "Dimensiune RAM"

**Tabela `product_characteristics_values`** (tabelă de legătură pentru valorile caracteristicilor unui produs)
*   `id` (INT, PK, AI)
*   `product_id` (INT, FK to `products.id`)
*   `characteristic_id` (INT, FK to `characteristics.id`)
*   `value` (VARCHAR) - Ex: "Roșu", "Bumbac", "16GB"
*   UNIQUE (`product_id`, `characteristic_id`) - Un produs nu poate avea aceeași caracteristică de două ori

## 6. Schema Logică a Aplicației

```mermaid
graph TD
    subgraph Browser Client
        User[Utilizator Client]
    end

    subgraph Browser Admin
        AdminUser[Utilizator Admin]
    end

    subgraph Web Server (Apache/Nginx + PHP Engine)
        AppCore{Aplicație PHP}
        RouterUser["Router Public (index.php, products_list.php, etc.)"]
        RouterAdmin["Router Admin (admin/index.php, add_product.php, etc.)"]
        Includes["Fișiere Include (db.php, functions.php, headers, footers)"]
        AuthUser["Autentificare Utilizator (login, register)"]
        AuthAdmin["Autentificare Admin (admin/login)"]
        ProductDisplay["Logică Afișare Produse (list, detail)"]
        ProductAdmin["Logică Admin Produse (CRUD, imagini)"]
        CharAdmin["Logică Admin Caracteristici (CRUD, asignare)"]
        ImageProc["Procesare Imagini (resizeImage)"]
    end

    subgraph Database Server
        DB[(MySQL Bază de Date: StoreDB)]
    end

    subgraph File System
        UploadsDir[/uploads/]
    end

    %% Flux Utilizator Client
    User -- HTTP Request --> RouterUser
    RouterUser -- Include --> Includes
    RouterUser -- Interacționează cu --> ProductDisplay
    RouterUser -- Interacționează cu --> AuthUser
    AuthUser -- CRUD Utilizatori --> DB
    ProductDisplay -- Citește Produse/Caracteristici --> DB
    ProductDisplay -- Citește Căi Imagini --> DB
    AppCore -- Trimite HTML/CSS/JS --> User

    %% Flux Utilizator Admin
    AdminUser -- HTTP Request --> RouterAdmin
    RouterAdmin -- Include --> Includes
    RouterAdmin -- Necesită --> AuthAdmin
    AuthAdmin -- Verifică Admin --> DB
    RouterAdmin -- Interacționează cu --> ProductAdmin
    RouterAdmin -- Interacționează cu --> CharAdmin
    ProductAdmin -- CRUD Produse --> DB
    CharAdmin -- CRUD Caracteristici --> DB
    ProductAdmin -- Lucrează cu Imagini --> ImageProc
    ImageProc -- Salvează/Citește Imagini --> UploadsDir
    AppCore -- Trimite HTML/CSS/JS Admin --> AdminUser

    %% Dependențe Comune
    Includes -- Utilizează --> DB
    ProductAdmin -- Utilizează Căi Imagini din --> DB
    ImageProc -- Generează Căi Imagini pentru --> DB
