class DrawingApp {
    constructor() {
        this.canvas = document.getElementById('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.adjustCanvasResolution();

        this.drawing = false;
        this.lastX = 0;
        this.lastY = 0;
        this.history = [];
        this.redoHistory = [];
        this.autoSmooth = false;
        this.lineWidth = 2;
        this.svgContent = '';

        this.init();
    }

    init() {
        this.attachEventListeners();
    }

    attachEventListeners() {
        // Souris
        this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
        this.canvas.addEventListener('mouseup', () => this.stopDrawing());
        this.canvas.addEventListener('mousemove', (e) => this.drawMouse(e));
        // Tactile
        this.canvas.addEventListener("touchstart", (e) => this.handleTouchStart(e), { passive: false });
        this.canvas.addEventListener("touchmove", (e) => this.handleTouchMove(e), { passive: false });
        this.canvas.addEventListener("touchend", () => this.stopDrawing());
        // Contrôle
        document.getElementById('undo').addEventListener('click', () => this.undo());
        document.getElementById('redo').addEventListener('click', () => this.redo());
        document.getElementById('clear').addEventListener('click', () => this.clear());
        document.getElementById('toggleAutoSmooth').addEventListener('click', () => {
            this.autoSmooth = !this.autoSmooth;
            this.smoothButtonStatus();
        });
        document.getElementById('save').addEventListener('click', () => {this.saveDrawingToDatabase(this.generateSvgCentered(this.history))});
        document.getElementById('save_description').addEventListener('click', () => {this.saveDescriptionToDatabase();});
    }

    

    adjustCanvasResolution() {
        const rect = this.canvas.getBoundingClientRect();
        this.canvas.width = rect.width;
        this.canvas.height = rect.height;
    }

    getAdjustedCoordinates(e) {
        const rect = this.canvas.getBoundingClientRect();
        const scaleX = this.canvas.width / rect.width;
        const scaleY = this.canvas.height / rect.height;
        
        let clientX, clientY;
        if (e.touches && e.touches[0]) {
            const touch = e.touches[0];
            clientX = touch.clientX;
            clientY = touch.clientY;
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }

        return {
            offsetX: (clientX - rect.left) * scaleX,
            offsetY: (clientY - rect.top) * scaleY
        };
    }

    handleTouchStart(e) {
        e.preventDefault();
        const { offsetX, offsetY } = this.getAdjustedCoordinates(e);
        this.startDrawing({ offsetX, offsetY });
    }
    
    handleTouchMove(e) {
        e.preventDefault();
        const { offsetX, offsetY } = this.getAdjustedCoordinates(e);
        this.drawHandle({ offsetX, offsetY });
    }

    smoothButtonStatus() {
        const elSmoothButton = document.querySelector('#toggleAutoSmooth');
        if (this.autoSmooth) {
            elSmoothButton.classList.add('smooth-on');
        } else {
            elSmoothButton.classList.remove('smooth-on');
        }
    }
    drawMouse(e) {
        if (!this.drawing) return;
    
        const { offsetX, offsetY } = e; // Utilisation directe des coordonnées de la souris
    
        this.performDrawing(offsetX, offsetY);
    }
    
    drawHandle(e) {
        if (!this.drawing) return;
    
        const { offsetX, offsetY } = e; // Utilisation des coordonnées ajustées pour les événements tactiles
    
        this.performDrawing(offsetX, offsetY);
    }
    
    performDrawing(x, y) {
        this.ctx.lineWidth = this.lineWidth;
        this.ctx.beginPath();
        this.ctx.moveTo(this.lastX, this.lastY);
        this.ctx.lineTo(x, y);
        this.ctx.stroke();
        this.ctx.closePath();
    
        if (this.autoSmooth) {
            this.smoothStroke(x, y);
        } else {
            this.history[this.history.length - 1].push({ x, y });
        }
    
        this.lastX = x;
        this.lastY = y;
    }
    startDrawing(e) {
        this.drawing = true;
        this.lastX = e.offsetX;
        this.lastY = e.offsetY;

        this.history.push([{ x: this.lastX, y: this.lastY }]);
        this.redoHistory = [];
    }

    stopDrawing() {
        this.drawing = false;
        if (this.autoSmooth) {
            this.smooth();
        }
    }

    smoothStroke(x, y) {
        const last = this.history[this.history.length - 1];
        if (last.length > 1) {
            const prevPoint = last[last.length - 2];
            const newPoint = {
                x: (x + prevPoint.x) * 0.5,
                y: (y + prevPoint.y) * 0.5
            };
            last.push(newPoint);
        } else {
            last.push({ x, y });
        }
    }

    smooth() {
        if (this.history.length === 0) return;

        const lastStroke = this.history[this.history.length - 1];
        const smoothenedStroke = lastStroke.map((point, index, arr) => {
            if (index === 0 || index === arr.length - 1) return point;
            const prev = arr[index - 1];
            const next = arr[index + 1];
            return {
                x: (point.x + prev.x + next.x) / 3,
                y: (point.y + prev.y + next.y) / 3
            };
        });

        this.history[this.history.length - 1] = smoothenedStroke;
        this.redrawCanvas();
    }

    undo() {
        if (this.history.length > 0) {
            this.redoHistory.push(this.history.pop());
            this.redrawCanvas();
        }
    }

    redo() {
        if (this.redoHistory.length > 0) {
            this.history.push(this.redoHistory.pop());
            this.redrawCanvas();
        }
    }

    clear() {
        this.history = [];
        this.redoHistory = [];
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }

    redrawCanvas() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.history.forEach(stroke => {
            this.ctx.lineWidth = this.lineWidth;
            this.ctx.beginPath();
            this.ctx.moveTo(stroke[0].x, stroke[0].y);
            stroke.forEach(point => this.ctx.lineTo(point.x, point.y));
            this.ctx.stroke();
            this.ctx.closePath();
        });
    }

    generateSvgCentered(pointsArray) {
        if (pointsArray.length === 0) return '';
        const { centeredPointsArray, width, height } = this.centerPoints(pointsArray);

        const padding = Math.max(width, height) * 0.1;
        const viewBox = `${-width / 2 - padding} ${-height / 2 - padding} ${width + 2 * padding} ${height + 2 * padding}`;

        let svgContent = `<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="${viewBox}">`;

        centeredPointsArray.forEach(points => {
            svgContent += `<path d="M ${points.map(p => `${p.x} ${p.y}`).join(" L ")}" stroke="black" fill="none"/>`;
        });

        svgContent += `</svg>`;
        return svgContent;
    }

    centerPoints(pointsArray) {
        let allPoints = pointsArray.flat();

        const minX = Math.min(...allPoints.map(p => p.x));
        const maxX = Math.max(...allPoints.map(p => p.x));
        const minY = Math.min(...allPoints.map(p => p.y));
        const maxY = Math.max(...allPoints.map(p => p.y));

        const centerX = (minX + maxX) / 2;
        const centerY = (minY + maxY) / 2;

        const centeredPointsArray = pointsArray.map(points =>
            points.map(p => ({
                x: p.x - centerX,
                y: p.y - centerY
            }))
        );

        return {
            centeredPointsArray,
            width: maxX - minX,
            height: maxY - minY
        };
    }
    async saveDrawingToDatabase() {
        try {
            const id = localStorage.getItem('id');
            const draw_svg = this.generateSvgCentered(this.history);
            if (!draw_svg) {
                alert('Aucun dessin à sauvegarder.');
                return;
            }
            const response = await fetch('/app/save-drawing', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id, draw_svg })
            });
    
            if (response.ok) {
                const result = await response.json();
                alert('Dessin sauvegardé avec succès : ' + result.message);
            } else {
                const error = await response.json();
                alert('Erreur lors de la sauvegarde : ' + error.error);
            }
        } catch (err) {
            console.error('Erreur lors de la requête :', err);
            alert('Une erreur est survenue.');
        }
    }
    async saveDescriptionToDatabase() {
        const draw_description = document.getElementById('draw_description').value;
        if (!draw_description) {
            alert('Veuillez entrer une description.');
            return;
        }
        try {
            const id = localStorage.getItem('id');
            const response = await fetch('/app/save-description', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id, draw_description })
            });
    
            if (response.ok) {
                const result = await response.json();
                alert('Description sauvegardée avec succès : ' + result.message);
            } else {
                const error = await response.json();
                alert('Erreur lors de la sauvegarde : ' + error.error);
            }
        } catch (err) {
            console.error('Erreur lors de la requête :', err);
            alert('Une erreur est survenue.');
        }
    }
}

const app = new DrawingApp();