
// back qui gère la recherche de match
class DrawApp {
    constructor() {
        this.data = [];
        this.index = 0;
        this.randomDrawing = null;
        this.id = localStorage.getItem('id');
        this.token = localStorage.getItem('token');
        this.numberOfSuperMatch = 0;
        this.latitude = localStorage.getItem('latitude');
        this.longitude = localStorage.getItem('longitude');
        this.gender = localStorage.getItem('gender');
        this.search_gender = localStorage.getItem('search_gender');
        this.distanceMax = 50;
        this.dataFilter = [];
        this.dataShuffle = [];
        this.init();
        this.paypal();
    }

    async init() {
        this.data = await this.fetchUserDrawings();
        this.listenInputFilter();
        this.getNumberOfSuperMatch();
    }
       
// fonction pour afficher la valeur du filtre et recharger les dessins
    listenInputFilter() {
        const input = document.getElementById("distance");
        const output = document.getElementById("distanceValue");


        input.addEventListener("input", () => {
            this.distanceMax = input.value;

            // Filtrer les données
            this.dataFilter = this.filtrerParGenreEtDistance(
                this.data,
                this.search_gender,
                this.latitude,
                this.longitude,
                this.distanceMax
            );
           

            // Mélanger les dessins (et stocker le résultat)
            this.dataShuffle = this.shuffleArray(this.dataFilter);

            // Réinitialiser l'index si nécessaire
            this.index = 0;

            // Afficher le premier dessin
            if (this.dataShuffle.length > 0) {
                 document.querySelector('#draws-container').style.display = "";
                this.displayDrawing(this.dataShuffle[this.index]);
            } else {
              document.querySelector('#draws-container').style.display = "none";
            }
        });
    }

// fonction pour payer via paypal
    paypal () {
                paypal.Buttons({
            createOrder: (data, actions) => {
            return actions.order.create({
                purchase_units: [{
                amount: {
                    value: '9.99',
                   
                },
                 description: 'Achat de 1 SuperMatch - Application Draw Date'
                }]
            }); 
            },
            onApprove: async (data, actions) => {
                 try {
                const details = await actions.order.capture();
                alert(`Paiement réussi, merci ${details.payer.name.given_name} !`);
                await this.addSuperMatch();
            } catch (error) {
                console.error('Erreur pendant la validation du paiement ou l’ajout du superMatch :', error);
                alert("Une erreur est survenue après le paiement. Merci de contacter le support.");
            }
            },
            onError: (err) => {
                console.error("Erreur PayPal :", err);
                alert("Le paiement a échoué. Merci de réessayer ou d’utiliser un autre moyen.");
            }
        }).render('#paypal-button-container');
    }

// fonction qui récupère les dessins à trouver
    async fetchUserDrawings() {
        try {
            const response = await fetch(`/app/draws/${this.id}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                return await response.json();
            } else {
                const error = await response.json();
                alert('Erreur : ' + error.error);
            }
        } catch (err) {
            console.error('Erreur lors de la requête :', err);
            alert('Une erreur est survenue.');
        }
        return [];
    }

// Fonction pour calculer la distance entre deux coordonnées géographiques
    calculDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Rayon de la Terre en kilomètres
        const toRad = x => x * Math.PI / 180;

        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);

        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        const distance = R * c;
        return distance; // en kilomètres
    }

// Fonction filtrer par genre ET distance maximale
    filtrerParGenreEtDistance(data, genreRecherche, referenceLat, referenceLon, distanceMaxKm) {
        const latRef = parseFloat(referenceLat);
        const lonRef = parseFloat(referenceLon);
        const distanceFiltrable = !isNaN(latRef) && !isNaN(lonRef);

        return data
            .map(item => {
                const lat = parseFloat(item.latitude);
                const lon = parseFloat(item.longitude);

                if (distanceFiltrable && !isNaN(lat) && !isNaN(lon)) {
                    const dist = this.calculDistance(latRef, lonRef, lat, lon);
                    return { ...item, distance: dist };
                } else {
                    return { ...item, distance: 0 }; // distance fictive pour compatibilité
                }
            })
            .filter(item => {
                // Si distance filtrable, on vérifie qu'elle est inférieure à la distance max
                return !distanceFiltrable || item.distance <= distanceMaxKm;
            })
            .filter(item => {
                const genreItem = item.gender ? item.gender.toString().toLowerCase().trim() : "";
                const genreCherche = genreRecherche.toString().toLowerCase().trim();
                return genreItem === genreCherche;
            });
    }


// fonction pour mélanger les dessins
    shuffleArray(array) {
        if (!Array.isArray(array) || array.length <= 3) {
            return array;
        }

        const shuffled = [...array]; // copie pour éviter de modifier l'original

        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }

        return shuffled;
    }

// affiche les dessins
    displayDrawing(draw) {
        try {
        const drawsContainer = document.getElementById('draws-container');
        drawsContainer.innerHTML = '';
        const svgElement = document.createElement('div');
        svgElement.innerHTML = draw.draw_svg;
        drawsContainer.appendChild(svgElement);
        this.randomDrawing = draw;
        }  catch (error) {
            alert("Aucun nouveau dessin à afficher", error);
        }
    }
// passer au dessin suivant
    nextDrawing() {
        this.index = (this.index + 1) % this.data.length;
        this.displayDrawing(this.dataShuffle[this.index]);
        this.hideHint();
        this.hidedescription();
    }
// passser au dessin précédent
    previousDrawing() {
        this.index = (this.index - 1 + this.data.length) % this.data.length;
        this.displayDrawing(this.dataShuffle[this.index]);
        this.hideHint();
        this.hidedescription();
    }
// compare les desciptions
    compareDescriptions() {
        if (!this.randomDrawing) {
            alert('Aucun dessin à comparer. Veuillez d\'abord charger un dessin.');
            return;
        }
        const drawDescriptionTry = document.getElementById('draw_description_try').value.toLowerCase();
        const drawDescriptionFromServer = this.randomDrawing.draw_description.toLowerCase();

        const trimmedTry = drawDescriptionTry.trim();
        const trimmedServer = drawDescriptionFromServer.trim();

        if (trimmedTry === trimmedServer) {
            this.match();
            alert('vous avez matché !');
             window.location.reload();
        } else {
            
           this.displayHintTry();
        }
    }
// match avec un utilisateur. utilise un superMatch
    async superMatch() {
         const response = await fetch(`/app/superMatch/${this.id}`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 'superMatch': this.numberOfSuperMatch - 1 })
            });
            if (response.ok) {
                this.match();
                alert('vous avez matché !');
                window.location.reload();
            }
    }
// ajoute 20 superMatch si le paiement paypal est accepté
    async addSuperMatch() {
        this.numberOfSuperMatch += 20;
        const response = await fetch(`/app/superMatch/${this.id}`, {
            method: 'PATCH',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 'superMatch': this.numberOfSuperMatch })
        });
        if (response.ok) {
            alert('vous avez 20 supermatch en plus!');
            window.location.reload();
        }
    }
// récupere et affiche le nombre de supermatch restant si il est strictement positif
    async getNumberOfSuperMatch() {
        try{
             const response = await fetch(`/app/superMatch/${this.id}`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                }
            });
            if (response.ok) {
                const data = await response.json();
                this.numberOfSuperMatch = data;
                let superMatchElement = document.querySelector('.superMatch');
                if (this.numberOfSuperMatch != 0) {
                    superMatchElement.textContent = this.numberOfSuperMatch;
                    superMatchElement.style.visibility = "visible";  
                }
            } else {
                console.error('Erreur get number of supermatch');
            }
        } catch (error) {
            console.error('Erreur réseau :', error);
        }
    }
// enregistre le match dans le base de donnée si le dessin et la description correspondent
    async match() {
        try {
            const response = await fetch(`/app/match`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ user1_id: this.id, user2_id: this.randomDrawing.id })
            });

            if (response.ok) {
            
            } else {
                console.error('Erreur lors du match');
            }
        } catch (error) {
            console.error('Erreur réseau :', error);
        }
    }
// affiche l'indice
    displayHint() {
        const drawDescription = this.randomDrawing.draw_description;
        const hintText = this.hint(drawDescription);
        document.getElementById('hint').innerText = hintText;
    }
// affiche les lettres qui corespondent entre l'essai et la vraie description
    displayHintTry() {  
        const tryInput = document.getElementById('draw_description_try').value;
        const hintText = this.hintTry(this.randomDrawing.draw_description, tryInput);
        document.getElementById('hint').innerText = hintText;
    }
// cache l'indice
    hideHint() {
        document.getElementById('hint').innerText = '';
    }
// cache la description
    hidedescription() {
        document.getElementById('draw_description_try').value = '';
    }
// fonction utilisée avec display hint. elle gère les espaces
    hint(description) {
        return description
            .split(' ')
            .map(word => word.charAt(0) + word.slice(1).split('').map(letter => '.').join(''))
            .join(' ');
    }
// compare la description et l'essai
    hintTry(description, tryInput) {
        const descriptionArray = description.toLowerCase().split('');
        const tryArray = tryInput.toLowerCase().split('');
    
        return descriptionArray
            .map((char, index) => {
                if (char === ' ') {
                    return ' ';
                } else if (char === tryArray[index]) {
                    return char;
                } else {
                    return '.';
                }
            })
            .join('');
    }
// gère la deconnexion
    logout() {
        localStorage.removeItem('id');
        localStorage.removeItem('token');
        window.location.href = '/app/src/login.html';
    }
}

const app = new DrawApp();

document.getElementById('hintButton').onclick = () => app.displayHint();
document.getElementById('nextButton').onclick = () => app.nextDrawing();
document.getElementById('previousButton').onclick = () => app.previousDrawing();
document.getElementById('compareButton').onclick = () => app.compareDescriptions();
document.getElementById('logoutButton').onclick = () => app.logout();
document.getElementById('superMatchButton').onclick = () => app.superMatch();