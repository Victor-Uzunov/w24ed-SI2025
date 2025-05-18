class ProgramEditor {
    constructor() {
        this.initializeElements();
        this.bindEvents();
        this.courses = [];
        this.dependencies = [];
        this.isDirty = false;
        this.showWelcomeMessage();
    }

    initializeElements() {
        // Buttons
        this.newProgramBtn = document.getElementById('newProgramBtn');
        this.loadProgramBtn = document.getElementById('loadProgramBtn');
        this.saveProgramBtn = document.getElementById('saveProgramBtn');
        this.exportProgramBtn = document.getElementById('exportProgramBtn');
        this.addCourseBtn = document.getElementById('addCourseBtn');

        // Forms and containers
        this.programBasicForm = document.getElementById('programBasicForm');
        this.coursesList = document.getElementById('coursesList');
        this.courseTemplate = document.getElementById('courseTemplate');

        // Add tooltips
        this.newProgramBtn.setAttribute('data-tooltip', 'Създай нова учебна програма');
        this.loadProgramBtn.setAttribute('data-tooltip', 'Зареди съществуваща програма');
        this.saveProgramBtn.setAttribute('data-tooltip', 'Запази текущата програма');
        this.exportProgramBtn.setAttribute('data-tooltip', 'Експортирай като PDF');
        this.addCourseBtn.setAttribute('data-tooltip', 'Добави нова дисциплина');
    }

    bindEvents() {
        this.newProgramBtn.addEventListener('click', () => this.newProgram());
        this.loadProgramBtn.addEventListener('click', () => this.loadProgram());
        this.saveProgramBtn.addEventListener('click', () => this.saveProgram());
        this.exportProgramBtn.addEventListener('click', () => this.exportProgram());
        this.addCourseBtn.addEventListener('click', () => this.addCourse());

        // Add form change listeners
        this.programBasicForm.addEventListener('change', () => this.setDirty());
        this.coursesList.addEventListener('change', () => this.setDirty());

        // Add window beforeunload event
        window.addEventListener('beforeunload', (e) => {
            if (this.isDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 's':
                        e.preventDefault();
                        this.saveProgram();
                        break;
                    case 'o':
                        e.preventDefault();
                        this.loadProgram();
                        break;
                    case 'n':
                        e.preventDefault();
                        this.newProgram();
                        break;
                }
            }
        });
    }

    showWelcomeMessage() {
        const message = document.createElement('div');
        message.className = 'success-message fade-in';
        message.textContent = 'Добре дошли в редактора на учебни програми!';
        document.querySelector('.container').insertBefore(message, document.querySelector('section'));
        
        setTimeout(() => message.remove(), 3000);
    }

    showMessage(text, type = 'success') {
        const message = document.createElement('div');
        message.className = `${type}-message fade-in`;
        message.textContent = text;
        document.querySelector('.container').insertBefore(message, document.querySelector('section'));
        
        setTimeout(() => message.remove(), 3000);
    }

    setDirty() {
        this.isDirty = true;
        this.saveProgramBtn.style.backgroundColor = 'var(--warning-color)';
    }

    clearDirty() {
        this.isDirty = false;
        this.saveProgramBtn.style.backgroundColor = '';
    }

    validateProgram() {
        const name = document.getElementById('programName').value;
        if (!name) {
            throw new Error('Моля, въведете име на програмата');
        }

        if (this.courses.length === 0) {
            throw new Error('Моля, добавете поне една дисциплина');
        }

        this.courses.forEach(course => {
            if (!course.name) {
                throw new Error('Всички дисциплини трябва да имат име');
            }
            if (!course.credits || course.credits < 0) {
                throw new Error(`Дисциплината "${course.name}" трябва да има валиден брой кредити`);
            }
        });

        // Validate dependencies
        const courseNames = new Set(this.courses.map(c => c.name));
        this.dependencies.forEach(dep => {
            if (!courseNames.has(dep.from)) {
                throw new Error(`Невалидна зависимост: дисциплината "${dep.from}" не съществува`);
            }
            if (!courseNames.has(dep.to)) {
                throw new Error(`Невалидна зависимост: дисциплината "${dep.to}" не съществува`);
            }
        });
    }

    newProgram() {
        if (this.isDirty && !confirm('Сигурни ли сте, че искате да създадете нова програма? Всички незапазени промени ще бъдат изгубени.')) {
            return;
        }

        this.courses = [];
        this.dependencies = [];
        this.programBasicForm.reset();
        this.coursesList.innerHTML = '';
        this.updateDependencyGraph();
        this.clearDirty();
        this.showMessage('Създадена е нова програма');
    }

    async loadProgram() {
        if (this.isDirty && !confirm('Сигурни ли сте, че искате да заредите друга програма? Всички незапазени промени ще бъдат изгубени.')) {
            return;
        }

        try {
            this.setLoading(true);
            const response = await fetch('php/load_program.php');
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('programName').value = data.program.name;
                document.getElementById('programType').value = data.program.type;
                
                this.courses = data.program.courses;
                this.dependencies = data.program.dependencies;
                
                this.renderCourses();
                this.updateDependencyGraph();
                this.clearDirty();
                this.showMessage('Програмата е заредена успешно');
            } else {
                this.showMessage(data.message, 'error');
            }
        } catch (error) {
            console.error('Error loading program:', error);
            this.showMessage('Възникна грешка при зареждане на програмата', 'error');
        } finally {
            this.setLoading(false);
        }
    }

    async saveProgram() {
        try {
            this.validateProgram();

            const programData = {
                name: document.getElementById('programName').value,
                type: document.getElementById('programType').value,
                courses: this.courses,
                dependencies: this.dependencies
            };

            this.setLoading(true);
            const response = await fetch('php/save_program.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(programData)
            });

            const data = await response.json();
            
            if (data.success) {
                this.clearDirty();
                this.showMessage('Програмата е запазена успешно');
            } else {
                this.showMessage(data.message, 'error');
            }
        } catch (error) {
            console.error('Error saving program:', error);
            this.showMessage(error.message || 'Възникна грешка при запазване на програмата', 'error');
        } finally {
            this.setLoading(false);
        }
    }

    async exportProgram() {
        try {
            this.validateProgram();
            this.setLoading(true);

            const response = await fetch('php/export_program.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: document.getElementById('programName').value,
                    type: document.getElementById('programType').value,
                    courses: this.courses,
                    dependencies: this.dependencies
                })
            });

            if (!response.ok) {
                throw new Error('Грешка при експортиране');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'program_export.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            this.showMessage('Програмата е експортирана успешно');
        } catch (error) {
            console.error('Error exporting program:', error);
            this.showMessage(error.message || 'Възникна грешка при експортиране на програмата', 'error');
        } finally {
            this.setLoading(false);
        }
    }

    addCourse() {
        const courseElement = this.courseTemplate.content.cloneNode(true);
        const courseItem = courseElement.querySelector('.course-item');
        
        courseItem.classList.add('fade-in');
        
        // Add event listeners
        courseItem.querySelector('.remove-course').addEventListener('click', (e) => {
            if (confirm('Сигурни ли сте, че искате да премахнете тази дисциплина?')) {
                const courseElement = e.target.closest('.course-item');
                courseElement.classList.add('fade-out');
                setTimeout(() => {
                    courseElement.remove();
                    this.updateCourses();
                }, 300);
            }
        });

        // Add input validation
        courseItem.querySelector('.course-credits').addEventListener('input', (e) => {
            const value = parseInt(e.target.value);
            if (value < 0) e.target.value = 0;
        });

        this.coursesList.appendChild(courseElement);
        this.updateCourses();
        this.setDirty();
    }

    setLoading(isLoading) {
        const buttons = document.querySelectorAll('button');
        if (isLoading) {
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('loading');
            });
        } else {
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.classList.remove('loading');
            });
        }
    }

    renderCourses() {
        this.coursesList.innerHTML = '';
        this.courses.forEach(course => {
            const courseElement = this.courseTemplate.content.cloneNode(true);
            const courseItem = courseElement.querySelector('.course-item');
            
            courseItem.querySelector('.course-name').value = course.name;
            courseItem.querySelector('.course-semester').value = course.semester;
            courseItem.querySelector('.course-credits').value = course.credits;
            courseItem.querySelector('.course-type').value = course.type;
            
            courseItem.querySelector('.remove-course').addEventListener('click', (e) => {
                if (confirm('Сигурни ли сте, че искате да премахнете тази дисциплина?')) {
                    const courseElement = e.target.closest('.course-item');
                    courseElement.classList.add('fade-out');
                    setTimeout(() => {
                        courseElement.remove();
                        this.updateCourses();
                    }, 300);
                }
            });

            this.coursesList.appendChild(courseElement);
        });
    }

    updateCourses() {
        this.courses = Array.from(this.coursesList.querySelectorAll('.course-item')).map(item => ({
            name: item.querySelector('.course-name').value,
            semester: parseInt(item.querySelector('.course-semester').value),
            credits: parseInt(item.querySelector('.course-credits').value) || 0,
            type: item.querySelector('.course-type').value
        }));

        this.updateDependencyGraph();
        this.setDirty();
    }

    updateDependencyGraph() {
        if (window.updateGraph) {
            window.updateGraph(this.courses, this.dependencies);
        }
    }
}

// Initialize the application when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.programEditor = new ProgramEditor();
}); 