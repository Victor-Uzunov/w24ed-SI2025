class DependencyGraph {
    constructor(container) {
        this.container = container;
        this.svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        this.container.appendChild(this.svg);
        this.nodes = [];
        this.edges = [];
        this.isDragging = false;
        this.selectedNode = null;
        this.offset = { x: 0, y: 0 };
        this.isCreatingDependency = false;
        this.dependencyStartNode = null;
        this.scale = 1;
        this.viewBox = { x: 0, y: 0, width: 800, height: 600 };
        
        this.initializeSVG();
        this.bindEvents();
        this.addZoomControls();
    }

    initializeSVG() {
        this.svg.setAttribute('width', '100%');
        this.svg.setAttribute('height', '100%');
        this.updateViewBox();

        // Add definitions for markers and gradients
        const defs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
        
        // Arrow marker
        const marker = document.createElementNS("http://www.w3.org/2000/svg", "marker");
        marker.setAttribute('id', 'arrowhead');
        marker.setAttribute('markerWidth', '10');
        marker.setAttribute('markerHeight', '7');
        marker.setAttribute('refX', '9');
        marker.setAttribute('refY', '3.5');
        marker.setAttribute('orient', 'auto');
        
        const polygon = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
        polygon.setAttribute('points', '0 0, 10 3.5, 0 7');
        polygon.setAttribute('fill', '#1a73e8');
        
        marker.appendChild(polygon);
        defs.appendChild(marker);
        
        // Hover effect gradient
        const gradient = document.createElementNS("http://www.w3.org/2000/svg", "linearGradient");
        gradient.setAttribute('id', 'nodeHoverGradient');
        gradient.setAttribute('x1', '0%');
        gradient.setAttribute('y1', '0%');
        gradient.setAttribute('x2', '100%');
        gradient.setAttribute('y2', '100%');
        
        const stop1 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
        stop1.setAttribute('offset', '0%');
        stop1.setAttribute('style', 'stop-color:rgba(26,115,232,0.1);stop-opacity:1');
        
        const stop2 = document.createElementNS("http://www.w3.org/2000/svg", "stop");
        stop2.setAttribute('offset', '100%');
        stop2.setAttribute('style', 'stop-color:rgba(26,115,232,0.2);stop-opacity:1');
        
        gradient.appendChild(stop1);
        gradient.appendChild(stop2);
        defs.appendChild(gradient);
        
        this.svg.appendChild(defs);

        // Add help text
        const helpText = document.createElementNS("http://www.w3.org/2000/svg", "text");
        helpText.setAttribute('x', '50%');
        helpText.setAttribute('y', '20');
        helpText.setAttribute('text-anchor', 'middle');
        helpText.setAttribute('class', 'help-text');
        helpText.textContent = 'Shift + Click за създаване на зависимости между дисциплини';
        this.svg.appendChild(helpText);
    }

    bindEvents() {
        this.svg.addEventListener('mousedown', this.handleMouseDown.bind(this));
        document.addEventListener('mousemove', this.handleMouseMove.bind(this));
        document.addEventListener('mouseup', this.handleMouseUp.bind(this));
        this.svg.addEventListener('click', this.handleClick.bind(this));
        
        // Add context menu for edge removal
        this.svg.addEventListener('contextmenu', this.handleContextMenu.bind(this));
    }

    handleClick(event) {
        if (!event.shiftKey) return;
        
        const node = event.target.closest('.node');
        if (!node) return;

        if (!this.isCreatingDependency) {
            this.isCreatingDependency = true;
            this.dependencyStartNode = node;
            this.highlightNode(node, true);
        } else {
            const endNode = node;
            if (endNode !== this.dependencyStartNode) {
                this.createDependency(
                    this.dependencyStartNode.getAttribute('data-id'),
                    endNode.getAttribute('data-id')
                );
            }
            this.highlightNode(this.dependencyStartNode, false);
            this.isCreatingDependency = false;
            this.dependencyStartNode = null;
        }
    }

    highlightNode(node, highlight) {
        const rect = node.querySelector('rect');
        if (highlight) {
            rect.setAttribute('stroke', 'var(--warning-color)');
            rect.setAttribute('stroke-width', '3');
        } else {
            rect.setAttribute('stroke', '#1a73e8');
            rect.setAttribute('stroke-width', '2');
        }
    }

    handleContextMenu(event) {
        event.preventDefault();
        const edge = event.target.closest('line');
        if (edge) {
            if (confirm('Премахване на зависимостта?')) {
                const from = edge.getAttribute('data-from');
                const to = edge.getAttribute('data-to');
                this.removeDependency(from, to);
            }
        }
    }

    createDependency(from, to) {
        // Check for circular dependencies
        if (this.wouldCreateCircularDependency(from, to)) {
            alert('Грешка: Това би създало циклична зависимост!');
            return;
        }

        // Check for semester order
        const fromCourse = window.programEditor.courses.find(c => c.name === from);
        const toCourse = window.programEditor.courses.find(c => c.name === to);
        
        if (fromCourse.semester >= toCourse.semester) {
            alert('Грешка: Зависимата дисциплина трябва да е в по-късен семестър!');
            return;
        }

        const edge = this.createEdge(from, to);
        this.edges.push(edge);
        this.svg.insertBefore(edge, this.svg.firstChild);
        this.updateEdges();

        // Update the program editor's dependencies
        if (window.programEditor) {
            window.programEditor.dependencies.push({ from, to });
            window.programEditor.setDirty();
        }
    }

    removeDependency(from, to) {
        const edge = this.edges.find(e => 
            e.getAttribute('data-from') === from && 
            e.getAttribute('data-to') === to
        );
        
        if (edge) {
            edge.remove();
            this.edges = this.edges.filter(e => e !== edge);
            
            // Update the program editor's dependencies
            if (window.programEditor) {
                window.programEditor.dependencies = window.programEditor.dependencies.filter(
                    d => !(d.from === from && d.to === to)
                );
                window.programEditor.setDirty();
            }
        }
    }

    wouldCreateCircularDependency(from, to) {
        const visited = new Set();
        const temp = new Set();

        const hasCycle = (node) => {
            if (temp.has(node)) return true;
            if (visited.has(node)) return false;

            temp.add(node);

            const dependencies = window.programEditor.dependencies
                .filter(d => d.from === node)
                .map(d => d.to);

            for (const dep of dependencies) {
                if (hasCycle(dep)) return true;
            }

            temp.delete(node);
            visited.add(node);
            return false;
        };

        // Temporarily add the new dependency
        window.programEditor.dependencies.push({ from, to });
        const result = hasCycle(from);
        // Remove the temporary dependency
        window.programEditor.dependencies.pop();

        return result;
    }

    handleMouseDown(event) {
        if (event.button !== 0) return; // Only handle left click
        
        const node = event.target.closest('.node');
        if (node) {
            if (event.shiftKey) {
                this.handleDependencyCreation(node);
            } else {
                this.startNodeDrag(node, event);
            }
        } else {
            this.startPan(event);
        }
    }

    startNodeDrag(node, event) {
        this.isDragging = true;
        this.selectedNode = node;
        const transform = node.getAttribute('transform');
        const [x, y] = transform.match(/translate\(([^,]+),([^)]+)\)/).slice(1).map(Number);
        
        this.offset = {
            x: event.clientX - x,
            y: event.clientY - y
        };
        
        node.classList.add('dragging');
    }

    startPan(event) {
        this.isPanning = true;
        this.panStart = {
            x: event.clientX,
            y: event.clientY,
            viewBoxX: this.viewBox.x,
            viewBoxY: this.viewBox.y
        };
    }

    handleMouseMove(event) {
        if (this.isDragging && this.selectedNode) {
            const x = event.clientX - this.offset.x;
            const y = event.clientY - this.offset.y;
            
            this.selectedNode.setAttribute('transform', `translate(${x},${y})`);
            this.updateEdges();
        } else if (this.isPanning) {
            const dx = (event.clientX - this.panStart.x) / this.scale;
            const dy = (event.clientY - this.panStart.y) / this.scale;
            
            this.viewBox.x = this.panStart.viewBoxX - dx;
            this.viewBox.y = this.panStart.viewBoxY - dy;
            this.updateViewBox();
        }
    }

    handleMouseUp() {
        if (this.selectedNode) {
            this.selectedNode.classList.remove('dragging');
        }
        this.isDragging = false;
        this.selectedNode = null;
        this.isPanning = false;
    }

    handleDependencyCreation(node) {
        if (!this.isCreatingDependency) {
            this.isCreatingDependency = true;
            this.dependencyStartNode = node;
            node.classList.add('dependency-source');
            
            // Add temporary guide line
            this.tempLine = document.createElementNS("http://www.w3.org/2000/svg", "line");
            this.tempLine.setAttribute('class', 'temp-dependency');
            this.tempLine.setAttribute('stroke', '#1a73e8');
            this.tempLine.setAttribute('stroke-width', '2');
            this.tempLine.setAttribute('stroke-dasharray', '5,5');
            this.svg.appendChild(this.tempLine);
            
            // Add mousemove listener for guide line
            this.guideLine = (e) => {
                const rect = this.svg.getBoundingClientRect();
                const x = (e.clientX - rect.left) / this.scale;
                const y = (e.clientY - rect.top) / this.scale;
                
                const startRect = this.dependencyStartNode.getBoundingClientRect();
                const startX = (startRect.left + startRect.width/2 - rect.left) / this.scale;
                const startY = (startRect.top + startRect.height/2 - rect.top) / this.scale;
                
                this.tempLine.setAttribute('x1', startX);
                this.tempLine.setAttribute('y1', startY);
                this.tempLine.setAttribute('x2', x);
                this.tempLine.setAttribute('y2', y);
            };
            document.addEventListener('mousemove', this.guideLine);
        } else {
            const endNode = node;
            if (endNode !== this.dependencyStartNode) {
                this.createDependency(
                    this.dependencyStartNode.getAttribute('data-id'),
                    endNode.getAttribute('data-id')
                );
            }
            this.dependencyStartNode.classList.remove('dependency-source');
            document.removeEventListener('mousemove', this.guideLine);
            if (this.tempLine) {
                this.tempLine.remove();
            }
            this.isCreatingDependency = false;
            this.dependencyStartNode = null;
        }
    }

    createNode(course, x, y) {
        const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
        g.classList.add('node');
        g.setAttribute('transform', `translate(${x},${y})`);
        g.setAttribute('data-id', course.name);

        const rect = document.createElementNS("http://www.w3.org/2000/svg", "rect");
        rect.setAttribute('width', '150');
        rect.setAttribute('height', '60');
        rect.setAttribute('rx', '5');
        rect.setAttribute('ry', '5');
        rect.setAttribute('fill', this.getCourseTypeColor(course.type));
        rect.setAttribute('stroke', '#1a73e8');
        rect.setAttribute('stroke-width', '2');

        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
        text.setAttribute('x', '75');
        text.setAttribute('y', '25');
        text.setAttribute('text-anchor', 'middle');
        text.setAttribute('fill', '#202124');
        text.textContent = course.name;

        const semesterText = document.createElementNS("http://www.w3.org/2000/svg", "text");
        semesterText.setAttribute('x', '75');
        semesterText.setAttribute('y', '45');
        semesterText.setAttribute('text-anchor', 'middle');
        semesterText.setAttribute('fill', '#5f6368');
        semesterText.textContent = `Семестър ${course.semester}`;

        g.appendChild(rect);
        g.appendChild(text);
        g.appendChild(semesterText);

        // Add tooltip with credits
        const title = document.createElementNS("http://www.w3.org/2000/svg", "title");
        title.textContent = `${course.credits} кредита`;
        g.appendChild(title);

        return g;
    }

    getCourseTypeColor(type) {
        switch (type) {
            case 'mandatory': return '#e8f0fe';  // Light blue for mandatory
            case 'optional': return '#fce8e6';   // Light red for optional
            case 'facultative': return '#e6f4ea'; // Light green for facultative
            default: return '#ffffff';
        }
    }

    createEdge(from, to) {
        const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
        line.setAttribute('marker-end', 'url(#arrowhead)');
        line.setAttribute('stroke', '#1a73e8');
        line.setAttribute('stroke-width', '2');
        line.setAttribute('data-from', from);
        line.setAttribute('data-to', to);
        line.setAttribute('class', 'dependency-edge');
        return line;
    }

    updateEdges() {
        this.edges.forEach(edge => {
            const fromNode = this.svg.querySelector(`[data-id="${edge.getAttribute('data-from')}"]`);
            const toNode = this.svg.querySelector(`[data-id="${edge.getAttribute('data-to')}"]`);
            
            if (fromNode && toNode) {
                const fromRect = fromNode.getBoundingClientRect();
                const toRect = toNode.getBoundingClientRect();
                const svgRect = this.svg.getBoundingClientRect();

                const x1 = fromRect.left + fromRect.width / 2 - svgRect.left;
                const y1 = fromRect.top + fromRect.height / 2 - svgRect.top;
                const x2 = toRect.left + toRect.width / 2 - svgRect.left;
                const y2 = toRect.top + toRect.height / 2 - svgRect.top;

                edge.setAttribute('x1', x1);
                edge.setAttribute('y1', y1);
                edge.setAttribute('x2', x2);
                edge.setAttribute('y2', y2);
            }
        });
    }

    update(courses, dependencies) {
        // Clear existing graph
        while (this.svg.lastChild) {
            this.svg.removeChild(this.svg.lastChild);
        }
        this.initializeSVG();
        this.nodes = [];
        this.edges = [];

        // Calculate node positions based on semesters
        const semesterGroups = {};
        courses.forEach(course => {
            if (!semesterGroups[course.semester]) {
                semesterGroups[course.semester] = [];
            }
            semesterGroups[course.semester].push(course);
        });

        // Position nodes by semester
        const semesterWidth = 200;
        const verticalSpacing = 100;
        
        Object.entries(semesterGroups).forEach(([semester, coursesInSemester], semesterIndex) => {
            coursesInSemester.forEach((course, courseIndex) => {
                const x = semesterIndex * semesterWidth + 50;
                const y = courseIndex * verticalSpacing + 50;
                
                const node = this.createNode(course, x, y);
                this.nodes.push(node);
                this.svg.appendChild(node);
            });
        });

        // Create edges for dependencies
        dependencies.forEach(dep => {
            const edge = this.createEdge(dep.from, dep.to);
            this.edges.push(edge);
            this.svg.insertBefore(edge, this.svg.firstChild);
        });

        this.updateEdges();
    }

    addZoomControls() {
        const controls = document.createElement('div');
        controls.className = 'graph-controls';
        controls.innerHTML = `
            <button class="zoom-in" title="Увеличи">+</button>
            <button class="zoom-out" title="Намали">-</button>
            <button class="zoom-fit" title="Напасни">⟲</button>
        `;
        
        this.container.appendChild(controls);
        
        controls.querySelector('.zoom-in').addEventListener('click', () => this.zoom(1.2));
        controls.querySelector('.zoom-out').addEventListener('click', () => this.zoom(0.8));
        controls.querySelector('.zoom-fit').addEventListener('click', () => this.zoomToFit());
        
        // Add mouse wheel zoom
        this.svg.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.8 : 1.2;
            this.zoom(delta);
        });
    }

    zoom(factor) {
        const oldScale = this.scale;
        this.scale *= factor;
        this.scale = Math.max(0.5, Math.min(2, this.scale)); // Limit zoom range
        
        // Adjust viewBox to zoom around center
        const dScale = this.scale / oldScale;
        const centerX = this.viewBox.x + this.viewBox.width / 2;
        const centerY = this.viewBox.y + this.viewBox.height / 2;
        
        this.viewBox.width /= dScale;
        this.viewBox.height /= dScale;
        this.viewBox.x = centerX - this.viewBox.width / 2;
        this.viewBox.y = centerY - this.viewBox.height / 2;
        
        this.updateViewBox();
    }

    zoomToFit() {
        if (this.nodes.length === 0) return;
        
        let minX = Infinity, minY = Infinity;
        let maxX = -Infinity, maxY = -Infinity;
        
        this.nodes.forEach(node => {
            const transform = node.getAttribute('transform');
            const [x, y] = transform.match(/translate\(([^,]+),([^)]+)\)/).slice(1).map(Number);
            minX = Math.min(minX, x);
            minY = Math.min(minY, y);
            maxX = Math.max(maxX, x + 150); // node width
            maxY = Math.max(maxY, y + 60);  // node height
        });
        
        const padding = 50;
        this.viewBox = {
            x: minX - padding,
            y: minY - padding,
            width: maxX - minX + padding * 2,
            height: maxY - minY + padding * 2
        };
        
        this.scale = 1;
        this.updateViewBox();
    }

    updateViewBox() {
        this.svg.setAttribute('viewBox', 
            `${this.viewBox.x} ${this.viewBox.y} ${this.viewBox.width} ${this.viewBox.height}`
        );
    }
}

// Initialize the graph when the window loads
window.addEventListener('load', () => {
    const container = document.getElementById('dependencyGraph');
    const graph = new DependencyGraph(container);
    
    // Make the updateGraph function available globally
    window.updateGraph = (courses, dependencies) => {
        graph.update(courses, dependencies);
    };
}); 