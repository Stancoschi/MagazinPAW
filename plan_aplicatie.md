# Plan Aplicație: Magazin Virtual

## Descriere Generală
Aplicație web pentru gestionarea și afișarea produselor unui magazin virtual, realizată în PHP și MySQL.

## Echipă și Roluri
*   **Student A:** Implementarea funcționalităților de adăugare, editare, ștergere produse, inclusiv managementul fotografiilor (încărcare, redimensionare).
*   **Student B:** Implementarea funcționalităților de management al caracteristicilor, asignarea caracteristicilor la produse, afișarea listei de produse și a paginii de detaliu pentru un produs.

## Structura Bazei de Date

*   **`products`**:
    *   `id` (INT, PK, AI)
    *   `name` (VARCHAR(255))
    *   `short_description` (TEXT)
    *   `detailed_description` (TEXT)
    *   `price` (DECIMAL(10,2))
    *   `main_photo_filename` (VARCHAR(255), NULLABLE) - Numele fișierului imaginii principale
    *   `created_at` (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
    *   `updated_at` (TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
*   **`product_photos`**:
    *   `id` (INT, PK, AI)
    *   `product_id` (INT, FK to products.id ON DELETE CASCADE)
    *   `filename` (VARCHAR(255)) - Numele fișierului imaginii secundare
    *   `is_main` (BOOLEAN DEFAULT 0) - (Alternativă la `main_photo_filename` în `products`)
*   **`characteristics`**: (Tipuri de caracteristici)
    *   `id` (INT, PK, AI)
    *   `name` (VARCHAR(100), UNIQUE) - Ex: "Culoare", "Mărime", "Material"
*   **`product_characteristics_values`**: (Valorile caracteristicilor pentru fiecare produs)
    *   `id` (INT, PK, AI)
    *   `product_id` (INT, FK to products.id ON DELETE CASCADE)
    *   `characteristic_id` (INT, FK to characteristics.id ON DELETE CASCADE)
    *   `value` (VARCHAR(255)) - Ex: "Roșu", "XL", "Bumbac"

## Descrierea Fișierelor PHP și Transferul de Date

### Director `admin/`
*   **`add_product.php`**: Formular HTML pentru introducerea datelor unui nou produs (nume, descrieri, preț) și încărcarea fotografiilor (principală și secundare). La submit, trimite datele către `process_add_product.php` prin metoda POST.
*   **`process_add_product.php`**: Primește datele de la `add_product.php`. Validează datele. Procesează imaginile (salvează originalul în `uploads/products/`, creează și salvează thumbnail în `uploads/products_thumbnails/`). Inserează datele produsului în tabela `products` și informațiile despre fotografii în `product_photos`. Redirecționează către `list_products_admin.php` sau `add_product.php` cu mesaj de succes/eroare.
*   **`list_products_admin.php`**: Afișează o listă tabelară a tuturor produselor din tabela `products`. Fiecare rând va avea link-uri către `edit_product.php?id=X` și `delete_product.php?id=X`.
*   **`edit_product.php`**: Formular similar cu `add_product.php`, pre-populat cu datele produsului cu ID-ul specificat (primit prin GET). Permite modificarea datelor și a fotografiilor. La submit, trimite datele către `process_edit_product.php` prin metoda POST.
*   **`process_edit_product.php`**: Primește datele de la `edit_product.php`. Validează. Actualizează datele în `products` și gestionează fotografiile (adăugare noi, ștergere vechi, actualizare principală) în `product_photos`. Redirecționează.
*   **`delete_product.php`**: Confirmă și apoi șterge produsul (și fotografiile asociate, și valorile caracteristicilor) din baza de date, pe baza ID-ului primit prin GET. Șterge și fișierele fizice ale imaginilor.
*   **`manage_characteristics.php`**: (Student B) Formular pentru adăugarea de noi tipuri de caracteristici (ex: "Culoare"). Afișează lista caracteristicilor existente cu opțiuni de editare/ștergere. Trimite date la `process_manage_characteristics.php`.
*   **`process_manage_characteristics.php`**: (Student B) Gestionează operațiile CRUD pentru tabela `characteristics`.
*   **`assign_characteristics.php`**: (Student B) Permite selectarea unui produs și apoi asignarea/modificarea valorilor pentru diverse tipuri de caracteristici (selectate din `characteristics`). Trimite date la `process_assign_characteristics.php`.
*   **`process_assign_characteristics.php`**: (Student B) Salvează/actualizează valorile caracteristicilor pentru un produs în tabela `product_characteristics_values`.

### Director `public/`
*   **`index.php`**: Poate fi o pagină de întâmpinare sau un alias pentru `products_list.php`.
*   **`products_list.php`**: (Student B) Afișează o listă/grilă cu toate produsele active. Pentru fiecare produs, afișează numele, prețul, imaginea principală (thumbnail) și un link către `product_detail.php?id=X`. Interoghează tabela `products`.
*   **`product_detail.php`**: (Student B) Afișează detaliile complete pentru un singur produs (ID primit prin GET). Include: nume, descrieri, preț, imaginea principală mare, galeria de imagini secundare (thumbnails clicabile pentru versiunea mare), lista caracteristicilor și valorile acestora. Interoghează `products`, `product_photos`, `product_characteristics_values` (cu JOIN pe `characteristics`).

### Director `includes/`
*   **`db.php`**: Stabilește conexiunea la baza de date MySQL folosind PDO. Variabila de conexiune `$pdo` este disponibilă global (sau returnată).
*   **`functions.php`**: Conține funcții reutilizabile, de ex: `resizeImage($sourcePath, $destinationPath, $width, $height)`.
*   **`header.php`**: Conține partea de început a HTML-ului (doctype, head, început body, meniu de navigație comun). Include `db.php` și `functions.php`. Pornește sesiunea (`session_start()`) dacă e necesar.
*   **`footer.php`**: Conține partea de final a HTML-ului (închidere tag-uri principale, scripturi JS comune).

### Transfer de Date
*   Între paginile PHP: prin `$_GET` (pentru ID-uri, acțiuni simple), `$_POST` (pentru formulare), `$_SESSION` (pentru mesaje de stare, autentificare - dacă se adaugă).
*   Cu baza de date: prin interogări SQL executate de scripturile PHP (SELECT, INSERT, UPDATE, DELETE).
*   Fișiere: Imaginile sunt transferate de la client la server prin formulare (`enctype="multipart/form-data"`) și gestionate de PHP (`$_FILES`, `move_uploaded_file()`).

## Tehnologii
*   Server: Debian 12.5 (sau compatibil)
*   Web Server: Apache2
*   Limbaj Server-Side: PHP (versiune recentă, ex: 8.x)
*   Bază de Date: MySQL / MariaDB
*   Client-Side: HTML5, CSS3, JavaScript (opțional, pentru interactivitate sporită, ex: lightbox imagini)

## Server de Referință
*   Laborator: Debian 12.5
*   Server Personal (acceptat): Ubuntu 20.04 / Centos 7.1
*   Pachet software necesar: `apache2`, `php`, `mysql-server`, `php-mysql`, `php-gd` (pentru procesare imagini).
