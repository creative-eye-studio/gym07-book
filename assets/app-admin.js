/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/admin/app.scss';

// start the Stimulus application
import './bootstrap';
import Sortable from 'sortablejs';

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import frLocale from '@fullcalendar/core/locales/fr';
import chroma from 'chroma-js';
import flatpickr from 'flatpickr';


/* TABS
--------------------------------------------*/
var tabs = require('tabs');
var container = document.querySelector('.tab-container');

if (container != null) {
    tabs(container);
}


/* BTN NAV
--------------------------------------------*/
var btnNav = document.querySelector('.btn-nav');
btnNav.addEventListener('click', function () {
    document.querySelector(".navigation").classList.toggle("opened");
})


/* AUTOCOMPLETE
--------------------------------------------*/
document.addEventListener('DOMContentLoaded', function () {

    var autocompleteInput = document.querySelector(".user-search");
    var suggestionList = document.getElementById('suggestion-list');

    if (autocompleteInput != undefined) {
        autocompleteInput.addEventListener('input', function () {
            var term = autocompleteInput.value;
            if (term.length >= 2) {
                // Modifier l'URL pour inclure le terme en tant que paramètre de requête
                var apiUrl = "/api/users/" + encodeURIComponent(term);

                fetch(apiUrl)
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        showSuggestions(data);
                    })
                    .catch(function (error) {
                        console.error("Erreur de récupération des suggestions :", error);
                    });
            } else {
                suggestionList.innerHTML = '';
            }

            function showSuggestions(suggestions) {
                // Effacer la liste précédente des suggestions
                suggestionList.innerHTML = '';

                // Vérifier si suggestions est un objet
                if (typeof suggestions === 'object' && suggestions !== null) {
                    var responses = Object.values(suggestions);

                    // Vérifier si suggestions est un tableau
                    if (Array.isArray(responses)) {
                        responses.forEach(response => {
                            var listItem = document.createElement('li');
                            var link = document.createElement('a');
                            link.textContent = response.name;
                            link.href = "/admin/users/regist-course/" + response.id;
                            listItem.appendChild(link);
                            suggestionList.appendChild(listItem);
                        });
                    } else {
                        console.error("La variable 'suggestions' n'est pas un tableau.");
                    }
                } else {
                    console.error("La réponse JSON ne contient pas une suggestion valide.");
                }
            }
        })
    }
})


/* CALENDAR
--------------------------------------------*/
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        fetch('/api/planning')
            .then(response => response.json())
            .then(data => {

                if (window.innerWidth > 640) {
                    var calendar = new Calendar(calendarEl, {
                        plugins: [dayGridPlugin],
                        locale: frLocale,
                        events: data,
                    });
                } else {
                    var calendar = new Calendar(calendarEl, {
                        plugins: [dayGridPlugin],
                        initialView: 'dayGridDay',
                        locale: frLocale,
                        events: data,
                    });
                }

                calendar.render();
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des événements:', error);
            });
    }


    var calendarElAdmin = document.getElementById('calendar-admin');
    if (calendarElAdmin) {
        fetch('/api/planning-admin')
            .then(response => response.json())
            .then(data => {
                if (window.innerWidth > 640) {
                    var calendar = new Calendar(calendarElAdmin, {
                        plugins: [dayGridPlugin],
                        locale: frLocale,
                        events: data,
                    });
                } else {
                    var calendar = new Calendar(calendarElAdmin, {
                        plugins: [dayGridPlugin],
                        initialView: 'dayGridDay',
                        locale: frLocale,
                        events: data,
                    });
                }


                calendar.render();
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des événements:', error);
            });
    }


});


/* SORTABLE JS
--------------------------------------------*/
const dragDropList = document.querySelector('#drag-drop-list');

if (dragDropList) {
    let subItems = dragDropList.querySelectorAll('.subitems');
    document.addEventListener('DOMContentLoaded', () => {
        // Items racines
        const sortable = new Sortable(dragDropList, {
            group: 'nested',
            animation: 150,
            onEnd: (event) => {
                // Mettre à jour l'ordre des éléments après le glisser-déposer
                const lines = dragDropList.querySelectorAll('.nav-item');
                lines.forEach((line, index) => {
                    line.dataset.order = index + 1;
                });
                changeOrderLinks();
            },
        });

        // Sous-items
        for (let i = 0; i < subItems.length; i++) {
            const elem = subItems[i];
            const subsortable = new Sortable(elem, {
                group: 'nested',
                animation: 150,
                onEnd: (event) => {
                    changeOrderLinks();
                },
            })
        }
    });
}

function changeOrderLinks() {
    const url = dragDropList.dataset.url;
    const lines = dragDropList.querySelectorAll('.nav-item');
    const orderData = Array.from(lines).map((line) => ({
        id: line.dataset.id,
        order: line.dataset.order,
        sublist: line.parentElement.getAttribute('data-sublist')
    }));
    console.log(orderData);

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData),
    })
        .then((response) => {
            console.log('Enregistrement de l\'ordre terminé : ', response);
        })
        .catch((error) => {
            console.error('Erreur lors de l\'enregistrement de l\'ordre :', error);
        });
}


/* SECTION - NAVIGATION
--------------------------------------------*/
// Sélecteur de menu
const navSelect = document.querySelector('.nav-select');
if (navSelect) {
    navSelect.addEventListener('change', function () {
        const navSelected = navSelect.value;
        window.location.href = '/admin/navigation/' + navSelected;
    })
}

// Suppression de menu
const menuRemove = document.querySelectorAll('.menu-remove');
if (menuRemove.length > 0) {
    menuRemove.forEach((menu) => {
        menu.addEventListener('click', () => {
            const menuId = menu.dataset.id;
            const urlDel = menu.dataset.removeurl;
            fetch(urlDel, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(menuId),
            })
                .then((response) => {
                    const navLinkRemoved = document.querySelector(`.menu-${menuId}`);
                    navLinkRemoved.style.display = 'none';
                    navLinkRemoved.style.visibility = 'hidden';
                    console.log('Suppression effectuée : ', response);
                })
                .catch((error) => {
                    console.error('Erreur lors de la suppression :', error);
                });
        });
    });
}

// Modification du lien
const navLinksUpdate = document.querySelectorAll('.nav-update');
const popupContainer = document.querySelector('#popup-container');
if (navLinksUpdate) {
    const popup = document.querySelector('#popup');
    navLinksUpdate.forEach((link) => {
        link.addEventListener('click', function () {
            const url = link.dataset.url;
            popupContainer.style.display = 'flex';
            popup.src = url;
            console.log(url);
        })

    })
}

if (popupContainer) {
    popupContainer.addEventListener('click', function () {
        popupContainer.style.display = 'none';
    })
}

// Drag and drop / Suppression du lien
const navLinksRemove = document.querySelectorAll('.nav-remove');
if (navLinksRemove.length > 0 && dragDropList) {
    const urlDel = dragDropList.dataset.urldel;
    navLinksRemove.forEach((link) => {
        link.addEventListener('click', () => {
            const linkId = link.dataset.id;
            fetch(urlDel, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(linkId),
            })
                .then((response) => {
                    const navLinkRemoved = document.querySelector(`.nav-link-${linkId}`);
                    navLinkRemoved.style.display = 'none';
                    navLinkRemoved.style.visibility = 'hidden';
                    console.log('Suppression effectuée : ', response);
                })
                .catch((error) => {
                    console.error('Erreur lors de la suppression :', error);
                });
        });
    });
}
