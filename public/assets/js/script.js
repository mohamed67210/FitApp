console.log('hello')
// changer couleur de la nav apres scroll
var navbar = document.querySelector('nav');
window.onscroll = function () {
    if (window.scrollY > 20) {
        navbar.classList.add('nav-active');
    } else {
        navbar.classList.remove('nav-active');
    }
}
// menu burger
var navLinks = document.getElementById("nav-links");
var btnMenu = document.getElementById("hamburger");
var body = document.body
btnMenu.addEventListener('click', function () {
    btnMenu.classList.toggle('is-active');
    navLinks.classList.toggle('open')
});
// animation after scroll (pour categories et section about)
// Changing the defaults
window.sr = ScrollReveal({ reset: true });
// Customizing a reveal set
sr.reveal('#about_section', { scale: 0.5 });
sr.reveal('.categorie_cart', { interval: 200, scale: 0.2, reset: true });
sr.reveal('.about_categorie', { interval: 600, scale: 0.2 });


// section about
window.onload = function () {
    const textAbout = "<br>Nous avons créé <strong>JUSTRAINE</strong> pour aider les <strong>Coachs</strong> à partager leurs connaissances avec un public plus large. Nous croyons que tout le monde peut bénéficier d'un <strong>Coaching</strong> de qualité, et nous voulons rendre cela accessible à tous."
    const textCoach = "<br>Êtes-vous prêt à atteindre de nouveaux sommets dans votre carrière de coach sportif ? Rejoignez notre plateforme de coaching sportif freelance et connectez-vous avec des clients à la recherche de professionnels passionnés et dévoués pour les aider à atteindre leurs objectifs de fitness."
    const textMembre = "<br>Vous êtes à la recherche d'un coach sportif compétent et passionné pour vous aider à atteindre vos objectifs de fitness ? Rejoignez notre plateforme de coaching sportif freelance et découvrez une communauté de coachs de talent prêts à vous accompagner dans votre parcours de transformation physique."


    var monTexte = document.getElementById("monTexte");
    var monTitle = document.getElementById("monTitle");
    var title = ["A PROPOS", "ETES-VOUS COACH ?", "VOUS ETES  A LA RECHERCHE D4UN PROGRAMME ?"]
    var messages = [textAbout,
        textCoach,
        textMembre]; // tableau des messages à afficher
    var index = 0; // index du message courant
    if (monTexte != null) {
        setInterval(function () {
            monTitle.innerHTML = title[index];
            monTexte.innerHTML = messages[index]; // affiche le message courant dans l'élément HTML
            index++; // passe au message suivant
            if (index >= messages.length) { // si on a affiché tous les messages, on repart au début
                index = 0;
            }
        }, 15000); // temps en millisecondes (5 secondes = 5000 millisecondes)
    }

}
// nouveauté slider 
var currentSlide = 0;
const prevButton = document.querySelector('.fa-chevron-left');
const nextButton = document.querySelector('.fa-chevron-right');
if (prevButton != null) {

    prevButton.addEventListener('click', function () {
        currentSlide--;
        showSlide();
    })
    nextButton.addEventListener('click', function () {
        currentSlide++;
        showSlide();
    })
    setInterval(showSlide, 0);
}

function showSlide() {
    const slides = document.querySelectorAll('.slide');
    // si on arrive a la fin du slide on retourne au debut
    if (currentSlide < 0) {
        currentSlide = slides.length - 1;
    }
    else if (currentSlide >= slides.length) {
        currentSlide = 0;
    }
    // masquer tous les slides et afficher seulement celui actuel
    slides.forEach(slide => slide.style.display = 'none');
    slides[currentSlide].style.display = "flex";
    // console.log(slides[currentSlide])
}





// afficher cacher formulaire ajout diplome das page profile
const btn = document.getElementById('diplome_form_btn')
const form = document.getElementById('diplome_form')
if (btn != null) {
    btn.addEventListener('click', function () {
        console.log('yesyesy')
        form.classList.toggle('open')
    })
}


// page membre bouton tous les cours /liste de souhaits
const modules_link = document.querySelector('.modules_link')
const favorie_link = document.querySelector('.favories_link')
const favorie_items = document.querySelector('.favories_container')
const modules_items = document.querySelector('.modules_container')
if (modules_link != null) {
    modules_link.addEventListener('click', function () {
        favorie_items.style.display = "none"
        modules_items.style.display = "flex"
    })
    favorie_link.addEventListener('click', function () {
        favorie_items.style.display = "flex"
        modules_items.style.display = "none"
    })
}


// menu coach ou admin au milieu de la navbar
const menuUser = document.getElementById('menu_user');
const menuUserLinks = document.getElementById('user_links');
if (menuUser != null) {
    menuUser.addEventListener('click', function () {
        menuUserLinks.classList.toggle('open')
    })
}

// popup supprission de categorie panel admin
// Sélectionne tous les liens de suppression avec la classe "delete-article"
const deleteLinks = document.querySelectorAll('.delete-article');
if (deleteLinks != null) {
    // Ajoute un gestionnaire d'événements de clic à chaque lien de suppression
    deleteLinks.forEach(link => {
        link.addEventListener('click', e => {
            // Empêche le comportement par défaut du lien de suppression
            e.preventDefault();

            // Affiche le popup de confirmation
            const confirmed = confirm('Êtes-vous sûr de vouloir supprimer cette categorie ?');

            // Si l'utilisateur a confirmé, redirige vers l'URL de suppression
            if (confirmed) {
                window.location.href = link.href;
            }
        });
    });
}
