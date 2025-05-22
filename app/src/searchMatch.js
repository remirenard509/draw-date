class DrawApp {
    constructor() {
        this.data = [];
        this.index = 0;
        this.randomDrawing = null;
        this.id = localStorage.getItem('id');
        this.token = localStorage.getItem('token');
        this.numberOfSuperMatch = 0;
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
        this.getNumberOfSuperMatch();
    }

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

    shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
    }

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
             window.location.href = 'profil.html';
        }
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
            this.match();
            alert('vous avez matché !');


        } else {
            
           this.displayHintTry();
        }
    }

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

const app = new DrawApp();

document.getElementById('hintButton').onclick = () => app.displayHint();
document.getElementById('nextButton').onclick = () => app.nextDrawing();
document.getElementById('previousButton').onclick = () => app.previousDrawing();
document.getElementById('compareButton').onclick = () => app.compareDescriptions();
document.getElementById('logoutButton').onclick = () => app.logout();
document.getElementById('superMatchButton').onclick = () => app.superMatch();