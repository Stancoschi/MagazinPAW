# Proiect PAW: Magazin Virtual (2024-2025)

Acest proiect reprezintă o aplicație web de tip magazin virtual, dezvoltată în PHP și MySQL, conform cerințelor. Aplicația permite utilizatorilor să vizualizeze produse, iar administratorilor să gestioneze inventarul și caracteristicile acestora.






## Cuprins

- [Proiect PAW: Magazin Virtual (2024-2025)](#proiect-paw-magazin-virtual-2024-2025)
  - [Cuprins](#cuprins)
  - [1. Descriere Generală](#1-descriere-generală)
  - [2. Funcționalități](#2-funcționalități)
    - [Partea Publică (Client)](#partea-publică-client)
    - [Partea de Administrare (Admin)](#partea-de-administrare-admin)
  - [3. Tehnologii Folosite](#3-tehnologii-folosite)
  - [5. Schema Bazei de Date (Conceptuală)](#5-schema-bazei-de-date-conceptuală)
  - [6. Instalare și Configurare](#6-instalare-și-configurare)


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
*   **Deconectare Admin (`admin/logout.php` ):**
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

## 6. Instalare și Configurare

1.  **Cerințe server:**
    *   Server web (Apache, Nginx) cu suport PHP. Se recomandă un sistem de operare Linux (ex: Debian, Ubuntu, CentOS).
    *   PHP (recomandat 7.4+) cu extensiile: `pdo_mysql`, `gd`, `mbstring`. Puteți verifica extensiile instalate cu `php -m` și le puteți instala cu `sudo apt install php-gd php-mysql php-mbstring` (pe Debian/Ubuntu).
    *   Server MySQL sau MariaDB.
    *   Acces la linia de comandă a serverului (SSH).
    *   (Opțional, dar recomandat pentru administrarea bazei de date) phpMyAdmin instalat și configurat.

2.  **Copiere fișiere proiect:**
    *   Transferați toate fișierele și directoarele proiectului pe serverul web. Locația standard pentru fișierele web pe un server Apache pe Debian/Ubuntu este `/var/www/html/`.
    *   Se recomandă crearea unui subdirector pentru proiect, de exemplu `/var/www/html/magazin-virtual/`.
    *   Puteți folosi comenzi precum `scp` sau un client FTP (ex: FileZilla, WinSCP) pentru a transfera fișierele.
    *   Exemplu dacă fișierele sunt pe mașina locală și vreți să le copiați în `/var/www/html/magazin-virtual/` pe server:
        ```bash
        # De pe mașina locală, dacă arhivați proiectul:
        # tar -czvf magazin-virtual.tar.gz ./directorul_proiectului_local
        # scp magazin-virtual.tar.gz utilizator_server@ip_server:/tmp/
        #
        # Apoi, pe server:
        # sudo mkdir -p /var/www/html/magazin-virtual
        # sudo tar -xzvf /tmp/magazin-virtual.tar.gz -C /var/www/html/magazin-virtual/
        # sudo chown -R www-data:www-data /var/www/html/magazin-virtual/ # Setează proprietarul corect
        ```
    *   Sau, dacă copiați direct fișierele:
        ```bash
        # Pe server, după ce ați creat directorul /var/www/html/magazin-virtual/
        # Asigurați-vă că aveți permisiunile corecte pentru a scrie în el, sau folosiți sudo
        # Copiați conținutul proiectului în acest director.
        ```

3.  **Creare bază de date și utilizator MySQL:**
    *   Conectați-vă la serverul MySQL ca utilizator root (sau un utilizator cu privilegii de creare baze de date și utilizatori):
        ```bash
        sudo mysql -u root -p
        ```
        (Vi se va cere parola root MySQL)
    *   Executați următoarele comenzi SQL pentru a crea baza de date `StoreDB` și utilizatorul `stanco` cu parola `stanco` (schimbați parola într-un mediu de producție!):
        ```sql
        CREATE DATABASE StoreDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        CREATE USER 'stanco'@'localhost' IDENTIFIED BY 'stanco';
        GRANT ALL PRIVILEGES ON StoreDB.* TO 'stanco'@'localhost';
        FLUSH PRIVILEGES;
        EXIT;
        ```
    *   **Notă:** `utf8mb4` și `utf8mb4_unicode_ci` sunt recomandate pentru suport complet Unicode.

4.  **Importarea datelor în baza de date:**
    *   Veți avea nevoie de un fișier de backup `.sql` care conține structura tabelelor și datele (cel puțin cele 100/300 de înregistrări pentru tabelul principal).
    *   **Metoda 1: Folosind phpMyAdmin (dacă este instalat):**
        1.  Accesați phpMyAdmin în browser (ex: `http://ip_server/phpmyadmin`).
        2.  Autentificați-vă (de preferință cu utilizatorul `stanco` creat mai devreme, sau cu root dacă `stanco` nu are încă permisiuni de login în phpMyAdmin, deși ar trebui să aibă dacă ați urmat pașii de mai sus).
        3.  Selectați baza de date `StoreDB` din panoul din stânga.
        4.  Mergeți la tab-ul "Import".
        5.  La secțiunea "File to import", apăsați pe "Browse..." (sau "Choose File") și selectați fișierul `.sql` de backup de pe calculatorul dumneavoastră.
        6.  Asigurați-vă că formatul este "SQL".
        7.  Apăsați butonul "Go" (sau "Import") din partea de jos a paginii și așteptați finalizarea importului.
    *   **Metoda 2: Folosind linia de comandă (recomandat pentru fișiere mari):**
        1.  Transferați fișierul `.sql` de backup pe server (ex: în `/tmp/backup_store.sql`).
        2.  Executați comanda (înlocuiți `backup_store.sql` cu numele fișierului dumneavoastră):
            ```bash
            mysql -u stanco -p StoreDB < /tmp/backup_store.sql
            ```
            (Vi se va cere parola pentru utilizatorul `stanco`)

5.  **Configurare aplicație:**
    *   Editați fișierul `includes/db.php` din structura proiectului copiat pe server (ex: `/var/www/html/magazin-virtual/includes/db.php`).
    *   Actualizați detaliile de conectare la baza de date dacă sunt diferite de cele default din fișier:
        ```php
        $db_host = 'localhost'; // De obicei 'localhost' dacă DB e pe același server
        $db_name = 'StoreDB';   // Numele bazei de date create
        $db_user = 'stanco';    // Utilizatorul DB creat
        $db_pass = 'stanco';    // Parola utilizatorului DB (cea setată la CREATE USER)
        ```

6.  **Permisiuni director `uploads/`:**
    *   Asigurați-vă că directorul `uploads/` și subdirectoarele sale (`products/`, `products_thumbnails/`) există în structura proiectului pe server și au permisiuni de scriere pentru utilizatorul sub care rulează serverul web (de obicei `www-data` pe Debian/Ubuntu).
    *   Din directorul rădăcină al proiectului pe server (ex: `/var/www/html/magazin-virtual/`):
        ```bash
        sudo mkdir -p uploads/products uploads/products_thumbnails
        sudo chown -R www-data:www-data uploads/
        sudo chmod -R 775 uploads/ # Permite scrierea pentru proprietar și grup
        ```

7.  **Configurare `BASE_URL` (dacă este necesar):**
    *   Funcția din `includes/functions.php` încearcă să determine automat `BASE_URL`.
    *   Dacă proiectul este accesat printr-un alias sau un subdirector complex care nu este detectat corect, s-ar putea să fie nevoie de ajustarea variabilei `$project_subdir` în `includes/functions.php`:
        ```php
        // în includes/functions.php
        $project_subdir = '/magazin-virtual'; // Modifică aici dacă e cazul, ex: '' dacă e la rădăcina domeniului
        define('BASE_URL', $protocol . $host . $project_subdir);
        ```
    *   De asemenea, configurați serverul web (Apache/Nginx) dacă este necesar (ex: VirtualHost pentru Apache) pentru a servi corect proiectul din `/var/www/html/magazin-virtual/`.

8.  **Accesare aplicație:**
    *   Deschideți browser-ul și navigați la URL-ul proiectului. Dacă ați instalat în `/var/www/html/magazin-virtual/`, URL-ul ar putea fi:
        *   `http://ip_server/magazin-virtual/public/` (pentru partea publică)
        *   `http://ip_server/magazin-virtual/admin/` (pentru panoul de admin)
    *   Asigurați-vă că fișierul `.htaccess` (dacă există și este necesar pentru URL rewriting) este permis și procesat de Apache (necesită `AllowOverride All` în configurația VirtualHost-ului și `mod_rewrite` activat). Codul furnizat nu pare să se bazeze pe `.htaccess` pentru funcționalitatea de bază.