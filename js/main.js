class App {
    constructor() {
        this.selectedProgramme = null;
        this.courses = [];
        this.setupEventListeners();
        UI.init();
        this.loadProgrammes();
    }

    setupEventListeners() {
        // Programme form events
        UI.elements.newProgrammeBtn.addEventListener('click', () => UI.showProgrammeForm());
        UI.elements.cancelProgrammeBtn.addEventListener('click', () => UI.hideProgrammeForm());
        UI.elements.createProgrammeForm.addEventListener('submit', (e) => this.handleProgrammeSubmit(e));

        // Programme list events
        UI.elements.programmesList.addEventListener('click', (e) => {
            const listItem = e.target.closest('.list-item');
            const deleteBtn = e.target.closest('.delete-programme');

            if (deleteBtn) {
                this.handleProgrammeDelete(deleteBtn.dataset.id);
            } else if (listItem) {
                this.handleProgrammeSelect(listItem.dataset.id);
            }
        });

        // Course form events
        UI.elements.newCourseBtn.addEventListener('click', () => UI.showCourseForm());
        UI.elements.cancelCourseBtn.addEventListener('click', () => UI.hideCourseForm());
        UI.elements.createCourseForm.addEventListener('submit', (e) => this.handleCourseSubmit(e));

        // Course year selection event for prerequisites
        UI.elements.createCourseForm.elements.available_year.addEventListener('change', (e) => {
            const selectedYear = parseInt(e.target.value);
            if (!isNaN(selectedYear)) {
                UI.updatePrerequisitesList(this.courses, selectedYear);
            }
        });

        // Course list events
        UI.elements.coursesList.addEventListener('click', (e) => {
            const deleteBtn = e.target.closest('.delete-course');
            if (deleteBtn) {
                this.handleCourseDelete(deleteBtn.dataset.id);
            }
        });

        // Handle network errors
        window.addEventListener('online', () => {
            UI.showToast('Connection restored', 'success');
            this.loadProgrammes();
        });

        window.addEventListener('offline', () => {
            UI.showToast('Connection lost', 'error');
        });
    }

    async loadProgrammes() {
        const panel = document.querySelector('.programmes-panel');
        try {
            UI.showLoading(panel);
            const programmes = await Api.getProgrammes();
            UI.renderProgrammes(programmes);
        } catch (error) {
            console.error('Failed to load programmes:', error);
            UI.showToast('Failed to load programmes', 'error');
        } finally {
            UI.hideLoading(panel);
        }
    }

    async handleProgrammeSubmit(e) {
        e.preventDefault();
        const form = e.target;
        UI.clearFormErrors(form);

        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            years_to_study: parseInt(formData.get('years_to_study')),
            type: formData.get('type')
        };

        try {
            UI.disableForm(form);
            await Api.createProgramme(data);
            UI.hideProgrammeForm();
            await this.loadProgrammes();
            UI.showToast('Programme created successfully', 'success');
        } catch (error) {
            console.error('Failed to create programme:', error);
            if (error.error?.details) {
                UI.showFormErrors(form, error.error.details);
            } else {
                UI.showToast('Failed to create programme', 'error');
            }
        } finally {
            UI.enableForm(form);
        }
    }

    async handleProgrammeSelect(id) {
        const panel = document.querySelector('.courses-panel');
        try {
            UI.showLoading(panel);
            const programme = await Api.getProgramme(id);
            this.selectedProgramme = programme;
            UI.showCourseContent(programme.name);
            this.courses = programme.courses || [];
            UI.renderCourses(this.courses);
        } catch (error) {
            console.error('Failed to load programme:', error);
            UI.showToast('Failed to load programme details', 'error');
        } finally {
            UI.hideLoading(panel);
        }
    }

    async handleProgrammeDelete(id) {
        const confirmed = await UI.showConfirmDialog(
            'Are you sure you want to delete this programme? All its courses will be deleted too.'
        );
        
        if (!confirmed) return;

        const panel = document.querySelector('.programmes-panel');
        try {
            UI.showLoading(panel);
            await Api.deleteProgramme(id);
            
            if (this.selectedProgramme?.id === id) {
                this.selectedProgramme = null;
                UI.hideCourseContent();
            }
            
            await this.loadProgrammes();
            UI.showToast('Programme deleted successfully', 'success');
        } catch (error) {
            console.error('Failed to delete programme:', error);
            UI.showToast('Failed to delete programme', 'error');
        } finally {
            UI.hideLoading(panel);
        }
    }

    async handleCourseSubmit(e) {
        e.preventDefault();
        if (!this.selectedProgramme) return;

        const form = e.target;
        UI.clearFormErrors(form);

        const formData = new FormData(form);
        const prerequisites = Array.from(form.elements.prerequisites.selectedOptions).map(option => option.value);
        
        const data = {
            name: formData.get('name'),
            credits: parseInt(formData.get('credits')),
            available_year: parseInt(formData.get('available_year')),
            description: formData.get('description'),
            prerequisites: prerequisites
        };

        const panel = document.querySelector('.courses-panel');
        try {
            UI.disableForm(form);
            UI.showLoading(panel);
            await Api.createCourse(this.selectedProgramme.id, data);
            UI.hideCourseForm();
            await this.handleProgrammeSelect(this.selectedProgramme.id);
            UI.showToast('Course created successfully', 'success');
        } catch (error) {
            console.error('Failed to create course:', error);
            if (error.error?.details) {
                UI.showFormErrors(form, error.error.details);
            } else {
                UI.showToast('Failed to create course', 'error');
            }
        } finally {
            UI.hideLoading(panel);
            UI.enableForm(form);
        }
    }

    async handleCourseDelete(id) {
        if (!this.selectedProgramme) return;

        const confirmed = await UI.showConfirmDialog(
            'Are you sure you want to delete this course? This action cannot be undone.'
        );
        
        if (!confirmed) return;

        const panel = document.querySelector('.courses-panel');
        try {
            UI.showLoading(panel);
            await Api.deleteCourse(this.selectedProgramme.id, id);
            await this.handleProgrammeSelect(this.selectedProgramme.id);
            UI.showToast('Course deleted successfully', 'success');
        } catch (error) {
            console.error('Failed to delete course:', error);
            UI.showToast('Failed to delete course', 'error');
        } finally {
            UI.hideLoading(panel);
        }
    }
}

// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
    new App();
}); 