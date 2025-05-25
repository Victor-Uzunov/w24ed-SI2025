class UI {
    static elements = {
        programmesList: document.getElementById('programmesList'),
        programmeForm: document.getElementById('programmeForm'),
        createProgrammeForm: document.getElementById('createProgrammeForm'),
        newProgrammeBtn: document.getElementById('newProgrammeBtn'),
        cancelProgrammeBtn: document.getElementById('cancelProgrammeBtn'),
        
        courseContent: document.getElementById('courseContent'),
        noProgrammeSelected: document.getElementById('noProgrammeSelected'),
        selectedProgrammeName: document.getElementById('selectedProgrammeName'),
        coursesList: document.getElementById('coursesList'),
        courseForm: document.getElementById('courseForm'),
        createCourseForm: document.getElementById('createCourseForm'),
        newCourseBtn: document.getElementById('newCourseBtn'),
        cancelCourseBtn: document.getElementById('cancelCourseBtn'),
        coursePrerequisites: document.getElementById('coursePrerequisites'),
        
        confirmDialog: document.getElementById('confirmDialog'),
        confirmMessage: document.getElementById('confirmMessage'),
        confirmYes: document.getElementById('confirmYes'),
        confirmNo: document.getElementById('confirmNo'),

        helpBtn: document.getElementById('helpBtn'),
        helpDialog: document.getElementById('helpDialog'),
        closeHelpBtn: document.getElementById('closeHelpBtn'),
        toast: document.getElementById('toast')
    };

    static init() {
        // Setup help dialog
        this.elements.helpBtn.addEventListener('click', () => this.showHelpDialog());
        this.elements.closeHelpBtn.addEventListener('click', () => this.hideHelpDialog());

        // Setup loading indicators
        const panels = document.querySelectorAll('.panel');
        panels.forEach(panel => {
            const loader = document.createElement('div');
            loader.className = 'loading-overlay hidden';
            loader.innerHTML = '<div class="loading-spinner"></div>';
            panel.appendChild(loader);
        });
    }

    static showElement(element) {
        element.classList.remove('hidden');
    }

    static hideElement(element) {
        element.classList.add('hidden');
    }

    static clearForm(form) {
        form.reset();
        form.querySelectorAll('.error').forEach(error => error.textContent = '');
        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.disabled = false;
        });
    }

    static disableForm(form) {
        form.querySelectorAll('input, select, textarea, button').forEach(input => {
            input.disabled = true;
        });
    }

    static enableForm(form) {
        form.querySelectorAll('input, select, textarea, button').forEach(input => {
            input.disabled = false;
        });
    }

    static showFormErrors(form, errors) {
        errors.forEach(error => {
            const errorElement = form.querySelector(`[data-for="${error.field}"]`);
            if (errorElement) {
                errorElement.textContent = error.message;
            }
        });
    }

    static clearFormErrors(form) {
        form.querySelectorAll('.error').forEach(error => error.textContent = '');
    }

    static showLoading(panel) {
        const loader = panel.querySelector('.loading-overlay');
        if (loader) {
            this.showElement(loader);
        }
    }

    static hideLoading(panel) {
        const loader = panel.querySelector('.loading-overlay');
        if (loader) {
            this.hideElement(loader);
        }
    }

    static renderProgrammes(programmes) {
        this.elements.programmesList.innerHTML = programmes.map(programme => `
            <div class="list-item" data-id="${programme.id}">
                <div class="list-item-content">
                    <div class="list-item-title">${programme.name}</div>
                    <div class="list-item-subtitle">
                        ${programme.years_to_study} years | ${programme.type}
                        ${programme.course_count ? ` | ${programme.course_count} course${programme.course_count !== 1 ? 's' : ''}` : ''}
                    </div>
                </div>
                <div class="item-actions">
                    <button class="btn danger delete-programme" data-id="${programme.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    static renderCourses(courses) {
        this.elements.coursesList.innerHTML = courses.map(course => `
            <div class="list-item">
                <div class="list-item-content">
                    <div class="list-item-title">${course.name}</div>
                    <div class="list-item-subtitle">
                        ${course.credits} credits | Year ${course.available_year}
                        ${course.description ? `<br>${course.description}` : ''}
                        ${course.prerequisites?.length ? `<br>Prerequisites: ${course.prerequisites.length} course${course.prerequisites.length !== 1 ? 's' : ''}` : ''}
                    </div>
                </div>
                <div class="item-actions">
                    <button class="btn danger delete-course" data-id="${course.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    static updatePrerequisitesList(courses, selectedYear) {
        this.elements.coursePrerequisites.innerHTML = courses
            .filter(course => course.available_year < selectedYear)
            .map(course => `
                <option value="${course.id}">${course.name} (Year ${course.available_year})</option>
            `).join('');
    }

    static async showConfirmDialog(message) {
        return new Promise((resolve) => {
            this.elements.confirmMessage.textContent = message;
            this.showElement(this.elements.confirmDialog);

            const handleYes = () => {
                cleanup();
                resolve(true);
            };

            const handleNo = () => {
                cleanup();
                resolve(false);
            };

            const cleanup = () => {
                this.hideElement(this.elements.confirmDialog);
                this.elements.confirmYes.removeEventListener('click', handleYes);
                this.elements.confirmNo.removeEventListener('click', handleNo);
            };

            this.elements.confirmYes.addEventListener('click', handleYes);
            this.elements.confirmNo.addEventListener('click', handleNo);
        });
    }

    static showHelpDialog() {
        this.showElement(this.elements.helpDialog);
    }

    static hideHelpDialog() {
        this.hideElement(this.elements.helpDialog);
    }

    static showToast(message, type = 'success', duration = 3000) {
        const toast = this.elements.toast;
        toast.textContent = message;
        toast.className = `toast ${type}`;
        this.showElement(toast);

        setTimeout(() => {
            this.hideElement(toast);
        }, duration);
    }

    static showProgrammeForm() {
        this.showElement(this.elements.programmeForm);
        this.elements.createProgrammeForm.elements.name.focus();
    }

    static hideProgrammeForm() {
        this.hideElement(this.elements.programmeForm);
        this.clearForm(this.elements.createProgrammeForm);
    }

    static showCourseForm() {
        this.showElement(this.elements.courseForm);
        this.elements.createCourseForm.elements.name.focus();
    }

    static hideCourseForm() {
        this.hideElement(this.elements.courseForm);
        this.clearForm(this.elements.createCourseForm);
    }

    static showCourseContent(programmeName) {
        this.hideElement(this.elements.noProgrammeSelected);
        this.showElement(this.elements.courseContent);
        this.elements.selectedProgrammeName.textContent = programmeName;
    }

    static hideCourseContent() {
        this.showElement(this.elements.noProgrammeSelected);
        this.hideElement(this.elements.courseContent);
        this.hideCourseForm();
    }
} 