class DrawApp {
    constructor() {
        this.data = [];
        this.index = 0;
        this.randomDrawing = null;
        this.init();
    }

    async init() {
        this.data = await this.fetchUserDrawings();
        if (!Array.isArray(this.data)) {
            console.error('Les données récupérées ne sont pas un tableau.');
            return;
        }
        this.shuffleArray(this.data);
        this.displayDrawing(this.data[this.index]);
    }

    async fetchUserDrawings() {
        try {
            const response = await fetch('/app/draws/', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
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

    shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
    }

    displayDrawing(draw) {
        const drawsContainer = document.getElementById('draws-container');
        drawsContainer.innerHTML = '';
        const svgElement = document.createElement('div');
        svgElement.innerHTML = draw.draw_svg;
        drawsContainer.appendChild(svgElement);
        this.randomDrawing = draw;
    }

    nextDrawing() {
        this.index = (this.index + 1) % this.data.length;
        this.displayDrawing(this.data[this.index]);
        this.hideHint();
        this.hidedescription();
    }

    previousDrawing() {
        this.index = (this.index - 1 + this.data.length) % this.data.length;
        this.displayDrawing(this.data[this.index]);
        this.hideHint();
        this.hidedescription();
    }

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
            alert('Les descriptions correspondent !');
        } else {
            
           this.displayHintTry();
        }
    }

    displayHint() {
        const drawDescription = this.randomDrawing.draw_description;
        const hintText = this.hint(drawDescription);
        document.getElementById('hint').innerText = hintText;
    }

    displayHintTry() {  
        const tryInput = document.getElementById('draw_description_try').value;
        const hintText = this.hintTry(this.randomDrawing.draw_description, tryInput);
        document.getElementById('hint').innerText = hintText;
    }

    hideHint() {
        document.getElementById('hint').innerText = '';
    }
    hidedescription() {
        document.getElementById('draw_description_try').value = '';
    }

    hint(description) {
        return description
            .split(' ')
            .map(word => word.charAt(0) + word.slice(1).split('').map(letter => '.').join(''))
            .join(' ');
    }
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

    logout() {
        localStorage.removeItem('id');
        localStorage.removeItem('token');
        window.location.href = '/app/src/login.html';
    }
}

// Instanciation de l'application
const app = new DrawApp();

// Liaison des boutons aux méthodes de la classe
document.getElementById('hintButton').onclick = () => app.displayHint();
document.getElementById('nextButton').onclick = () => app.nextDrawing();
document.getElementById('previousButton').onclick = () => app.previousDrawing();
document.getElementById('compareButton').onclick = () => app.compareDescriptions();
document.getElementById('logoutButton').onclick = () => app.logout();