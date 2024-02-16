=== PRISMA - CONTRIBUTIONS ===

Contributors: antonio@kiwop.com

Website: https://kiwop.com/

Tested in WP: Core 6.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin converts a CF7 form to the selected resource type (Apps, moodle, jclic, etc...)

== Dependencies ==

 You need Contact Form 7 (CF7) and Advanced Custom Fields (ACF) installed in your wordpress before activate this plugin

== Installation ==

e.g.

1. install plugin and activate it.
2. Once you have your form ready, create a new page and insert the form short code. Go to front page to test it.
3. Check that a new post han been created with the post type selected when you send the form.
4. Thumbnail image and attached file (if have been choosen) shoud be in the post created.


== CF7 - sample form ==

<label> Seleccioneu tipus de recurs
    [select* posttype "App" "JClic" "Moodle" "Classroom" "ExeLearning" "Kudis" "PDIs" "Projectes" "Scorm" "Videos" "En_linea" "Escrits"]
</label>

<label> Títol [text* titol] </label>

<label> Descripció [textarea* descripcio] </label>

<label> URL referència [url url_referencia] </label>

<label> Autor/s
    [textarea autors] </label>

<label> Etiquetes proposades
    [textarea etiquetes] </label>

<label> Imatge descatada (opcional)
[file Imatgedestacada limit:2MB filetypes:jpeg|jpg|png|webp] </label>

<label> fitxer del recurs (opcional)
[file Recurs limit:5MB filetypes:zip|pdf] </label>
<small>Només accepta zip i pdf, si el recurs tracta de diversos fitxers, empaqueta-ho tot en un zip.</small>
<br />
<label> El teu email [email* email-167] </label>

[submit "Enviar"]
