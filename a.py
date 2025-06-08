import mysql.connector
from mysql.connector import Error
import os
import shutil
import random
from faker import Faker # Importăm Faker
from PIL import Image # Pentru redimensionare imagini (Pillow)
import sys

# --- Configurare ---
DB_HOST = '192.168.234.128'
DB_USER = 'root'  # Folosește utilizatorul și parola corecte
DB_PASSWORD = 'stanco'
DB_NAME = 'StoreDB'

# Căi pentru imagini
# __file__ este calea scriptului curent
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SAMPLE_IMAGE_DIR = os.path.join(BASE_DIR, 'sample_images')
ORIGINAL_UPLOAD_DIR = os.path.join(BASE_DIR, 'uploads', 'products')
THUMBNAIL_UPLOAD_DIR = os.path.join(BASE_DIR, 'uploads', 'products_thumbnails')
THUMBNAIL_WIDTH = 150

NUMBER_OF_PRODUCTS = 550  # Numărul total de produse de adăugat

MAX_SECONDARY_PHOTOS_PER_PRODUCT = 4 # Numărul maxim de imagini secundare per produs

# Inițializează Faker
fake = Faker('ro_RO') # Date în format românesc

def resize_image(source_path, destination_path, width, height=None):
    """Redimensionează o imagine și o salvează."""
    try:
        img = Image.open(source_path)
        original_width, original_height = img.size

        if height is None:
            height = int((original_height / original_width) * width)

        # Pentru a păstra transparența PNG-urilor
        if img.mode in ("RGBA", "P"): # P poate avea paletă transparentă
            img = img.convert("RGBA")

        resized_img = img.resize((width, height), Image.Resampling.LANCZOS)

        # Salvează cu optimizare și calitate bună pentru JPEG
        if destination_path.lower().endswith(('.jpg', '.jpeg')):
            resized_img.save(destination_path, "JPEG", quality=85, optimize=True)
        elif destination_path.lower().endswith('.png'):
             # Pentru PNG, calitatea este controlată diferit, Pillow face o treabă bună default
            resized_img.save(destination_path, "PNG", optimize=True)
        else:
            resized_img.save(destination_path) # Alte formate
        return True
    except FileNotFoundError:
        print(f"Eroare: Fișierul sursă imagine nu a fost găsit: {source_path}")
        return False
    except Exception as e:
        print(f"Eroare la redimensionarea imaginii {source_path}: {e}")
        return False

def get_random_sample_image(sample_images_list):
    """Returnează o cale aleatorie către o imagine din lista de sample-uri."""
    if not sample_images_list:
        return None
    return random.choice(sample_images_list)

def main():
    # Creează directoarele de upload dacă nu există
    os.makedirs(ORIGINAL_UPLOAD_DIR, exist_ok=True)
    os.makedirs(THUMBNAIL_UPLOAD_DIR, exist_ok=True)

    if not os.path.isdir(SAMPLE_IMAGE_DIR):
        print(f"EROARE: Directorul 'sample_images' nu a fost găsit la calea: {SAMPLE_IMAGE_DIR}")
        print("Creați acest director și adăugați câteva imagini (.jpg, .png, .gif).")
        sys.exit(1)

    sample_images = [
        os.path.join(SAMPLE_IMAGE_DIR, f)
        for f in os.listdir(SAMPLE_IMAGE_DIR)
        if f.lower().endswith(('.png', '.jpg', '.jpeg', '.gif'))
    ]

    if not sample_images:
        print(f"EROARE: Nu au fost găsite imagini în directorul '{SAMPLE_IMAGE_DIR}'.")
        print("Adăugați câteva imagini și reîncercați.")
        sys.exit(1)

    connection = None
    try:
        connection = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )

        if connection.is_connected():
            cursor = connection.cursor()
            print("Se populează baza de date...")

            # 1. Adaugă tipuri de caracteristici (dacă nu există)
            characteristic_types = ['Culoare', 'Mărime', 'Material', 'Greutate', 'Dimensiuni (LxWxH)', 'Garanție', 'Producător']
            characteristic_colors = ['Roșu', 'Albastru', 'Verde', 'Galben', 'Negru', 'Alb', 'Gri', 'Portocaliu', 'Mov']
            characteristic_sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '36', '38', '40', '42', '44', 'Universală']
            characteristic_materials = ['Bumbac', 'Poliester', 'Lână', 'Mătase', 'Plastic', 'Metal', 'Lemn', 'Piele naturală', 'Piele ecologică']

            sql_insert_char_type = "INSERT IGNORE INTO characteristics (name) VALUES (%s)" # INSERT IGNORE pentru a evita erorile la duplicat
            for char_name in characteristic_types:
                cursor.execute(sql_insert_char_type, (char_name,))
            connection.commit()

            # Preluăm ID-urile caracteristicilor adăugate/existente
            cursor.execute("SELECT id, name FROM characteristics")
            db_characteristics = {name: id for id, name in cursor.fetchall()}


            # 2. Adaugă produse
            sql_insert_product = """
                INSERT INTO products (name, short_description, detailed_description, price, main_photo_filename)
                VALUES (%s, %s, %s, %s, %s)
            """
            sql_insert_photo = """
                INSERT INTO product_photos (product_id, filename, is_main)
                VALUES (%s, %s, %s)
            """
            sql_insert_prod_char_val = """
                INSERT INTO product_characteristics_values (product_id, characteristic_id, value)
                VALUES (%s, %s, %s)
            """

            for i in range(NUMBER_OF_PRODUCTS):
                product_name = f"{fake.bs().title()} {fake.color_name().title()} {random.randint(100, 2000)}"
                short_desc = fake.sentence(nb_words=10)
                detailed_desc = "\\n\\n".join(fake.paragraphs(nb=3))
                price = round(random.uniform(10.0, 5000.0), 2)

                # Adaugă imagine principală
                main_photo_filename_db = None
                random_image_source = get_random_sample_image(sample_images)
                if random_image_source:
                    _, ext = os.path.splitext(random_image_source)
                    main_photo_filename_fs = f"prod_main_pop_{fake.uuid4()}{ext}"
                    main_photo_path = os.path.join(ORIGINAL_UPLOAD_DIR, main_photo_filename_fs)
                    main_thumbnail_path = os.path.join(THUMBNAIL_UPLOAD_DIR, main_photo_filename_fs)

                    try:
                        shutil.copy(random_image_source, main_photo_path)
                        if resize_image(main_photo_path, main_thumbnail_path, THUMBNAIL_WIDTH):
                            main_photo_filename_db = main_photo_filename_fs
                        else:
                            os.remove(main_photo_path) # Sterge originalul daca thumbnail-ul esueaza
                    except Exception as e_img:
                        print(f"Eroare la procesarea imaginii principale: {e_img}")


                cursor.execute(sql_insert_product, (product_name, short_desc, detailed_desc, price, main_photo_filename_db))
                product_id = cursor.lastrowid # ID-ul produsului proaspăt inserat

                # Adaugă imagini secundare
                num_secondary = random.randint(0, MAX_SECONDARY_PHOTOS_PER_PRODUCT)
                for _ in range(num_secondary):
                    random_sec_image_source = get_random_sample_image(sample_images)
                    if random_sec_image_source:
                        _, ext_sec = os.path.splitext(random_sec_image_source)
                        sec_photo_filename_fs = f"prod_sec_pop_{fake.uuid4()}{ext_sec}"
                        sec_photo_path = os.path.join(ORIGINAL_UPLOAD_DIR, sec_photo_filename_fs)
                        sec_thumbnail_path = os.path.join(THUMBNAIL_UPLOAD_DIR, sec_photo_filename_fs)

                        try:
                            shutil.copy(random_sec_image_source, sec_photo_path)
                            if resize_image(sec_photo_path, sec_thumbnail_path, THUMBNAIL_WIDTH):
                                cursor.execute(sql_insert_photo, (product_id, sec_photo_filename_fs, 0))
                            else:
                                os.remove(sec_photo_path)
                        except Exception as e_img_sec:
                             print(f"Eroare la procesarea imaginii secundare: {e_img_sec}")


                # Adaugă caracteristici aleatorii produsului
                num_chars_for_product = random.randint(2, 5)
                # Creează o listă de nume de caracteristici și amestec-o
                available_char_names = list(db_characteristics.keys())
                random.shuffle(available_char_names)

                selected_chars_for_product = available_char_names[:num_chars_for_product]

                for char_name in selected_chars_for_product:
                    char_id = db_characteristics.get(char_name)
                    if char_id:
                        char_value = "N/A"
                        if char_name == 'Culoare': char_value = random.choice(characteristic_colors)
                        elif char_name == 'Mărime': char_value = random.choice(characteristic_sizes)
                        elif char_name == 'Material': char_value = random.choice(characteristic_materials)
                        elif char_name == 'Greutate': char_value = f"{round(random.uniform(0.1, 50.0), 2)} kg"
                        elif char_name == 'Dimensiuni (LxWxH)': char_value = f"{random.randint(10,100)}x{random.randint(10,100)}x{random.randint(5,50)} cm"
                        elif char_name == 'Garanție': char_value = f"{random.randint(1, 5)} ani"
                        elif char_name == 'Producător': char_value = fake.company()

                        try:
                            cursor.execute(sql_insert_prod_char_val, (product_id, char_id, char_value))
                        except mysql.connector.Error as err_char_val:
                            if err_char_val.errno == 1062: # Duplicate entry
                                pass # Ignoră eroarea de duplicat, se poate întâmpla rar dacă logica de selecție nu e perfect unică
                            else:
                                raise # Aruncă alte erori

                connection.commit() # Commit după fiecare produs (sau la intervale mai mari pentru performanță)

                if (i + 1) % 10 == 0:
                    print(f"Adăugat produs {i + 1}/{NUMBER_OF_PRODUCTS}")

            print(f"\\nBaza de date a fost populată cu succes cu {NUMBER_OF_PRODUCTS} produse!")
            print(f"Imaginile asociate au fost create în directoarele '{ORIGINAL_UPLOAD_DIR}' și '{THUMBNAIL_UPLOAD_DIR}'.")

    except Error as e:
        print(f"Eroare la conectarea la MySQL sau la operațiunile DB: {e}")
        if connection and connection.is_connected():
            connection.rollback() # Anulează tranzacția dacă a apărut o eroare
            print("Tranzacția a fost anulată.")
    except Exception as ex:
        print(f"O eroare neașteptată a apărut: {ex}")
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
            print("Conexiunea MySQL a fost închisă.")

if __name__ == '__main__':
    # Verifică și instalează Pillow dacă lipsește
    try:
        from PIL import Image
    except ImportError:
        print("Modulul Pillow (PIL) nu este instalat. Încerc să îl instalez...")
        try:
            import subprocess
            subprocess.check_call([sys.executable, "-m", "pip", "install", "Pillow"])
            print("Pillow a fost instalat. Rulează scriptul din nou.")
            from PIL import Image # Încearcă să reimportezi
        except Exception as e_pip:
            print(f"Nu s-a putut instala Pillow automat: {e_pip}")
            print("Te rog instalează manual: pip3 install Pillow")
            sys.exit(1)
    main()
