class ProgramEditor {
    constructor() {
        this.initializeElements();
        this.bindEvents();
        this.currentProgramId = null;
        this.nextCourseId = 1;
        this.loadPrograms();
        this.loadAllCourses();
    }

    initializeElements() {
        // Tabs
        this.tabButtons = document.querySelectorAll('.tab-btn');
        this.tabContents = document.querySelectorAll('.tab-content');

        // Buttons
        this.newProgramBtn = document.getElementById('newProgramBtn');
        this.saveProgramBtn = document.getElementById('saveProgramBtn');
        this.closeDialogBtn = document.getElementById('closeDialog');

        // Add new course dialog elements
        this.newCourseDialog = document.getElementById('newCourseDialog');
        if (!this.newCourseDialog) {
            // Create the dialog if it doesn't exist
            this.newCourseDialog = document.createElement('div');
            this.newCourseDialog.id = 'newCourseDialog';
            this.newCourseDialog.className = 'modal hidden';
            this.newCourseDialog.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="courseDialogTitle">Добавяне на нова дисциплина</h2>
                        <button class="close-modal" id="closeNewCourseDialog">×</button>
                    </div>
                    <form id="newCourseForm">
                        <input type="hidden" id="courseId" value="">
                        <div class="form-group">
                            <label for="newCourseName">Име на дисциплината</label>
                            <input type="text" id="newCourseName" required>
                        </div>
                        <div class="form-group">
                            <label for="newCourseCredits">Кредити</label>
                            <input type="number" id="newCourseCredits" min="1" max="9" required>
                        </div>
                        <div class="form-group">
                            <label for="newCourseYear">Година</label>
                            <select id="newCourseYear" required>
                                <option value="">Изберете година</option>
                                <option value="1">1-ва година</option>
                                <option value="2">2-ра година</option>
                                <option value="3">3-та година</option>
                                <option value="4">4-та година</option>
                                <option value="5">5-та година</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="newCourseProgramme">Програма</label>
                            <select id="newCourseProgramme" required>
                                <option value="">Изберете програма</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="newCourseDependsOn">Зависи от (ID на друга дисциплина)</label>
                            <input type="number" id="newCourseDependsOn" min="1">
                        </div>
                        <div class="button-group">
                            <button type="button" class="cancel-btn" id="cancelNewCourse">Отказ</button>
                            <button type="submit" class="save-btn">Запази</button>
                        </div>
                    </form>
                </div>
            `;
            document.body.appendChild(this.newCourseDialog);

            // Add event listeners for the new dialog
            this.newCourseDialog.querySelector('#closeNewCourseDialog').addEventListener('click', () => this.hideDialog(this.newCourseDialog));
            this.newCourseDialog.querySelector('#cancelNewCourse').addEventListener('click', () => this.hideDialog(this.newCourseDialog));
            this.newCourseDialog.querySelector('#newCourseForm').addEventListener('submit', (e) => this.saveNewCourse(e));
            this.newCourseDialog.addEventListener('click', (e) => {
                if (e.target === this.newCourseDialog) this.hideDialog(this.newCourseDialog);
            });
        }

        // Forms and containers
        this.programDialog = document.getElementById('programDialog');
        this.programBasicForm = document.getElementById('programBasicForm');
        this.programsList = document.getElementById('programsList');
        this.programsContainer = document.getElementById('programsContainer');
        this.allCoursesContainer = document.getElementById('allCoursesContainer');
        this.noProgramsMessage = document.getElementById('noProgramsMessage');
        this.noCoursesMessage = document.getElementById('noCoursesMessage');
    }

    bindEvents() {
        // Tab switching
        this.tabButtons.forEach(button => {
            button.addEventListener('click', () => this.switchTab(button.dataset.tab));
        });

        this.newProgramBtn.addEventListener('click', () => this.showNewProgramDialog());
        
        this.programBasicForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveProgram();
        });
        
        this.closeDialogBtn.addEventListener('click', () => this.hideDialog(this.programDialog));

        // Close dialogs when clicking outside
        this.programDialog.addEventListener('click', (e) => {
            if (e.target === this.programDialog) this.hideDialog(this.programDialog);
        });
    }

    switchTab(tabId) {
        this.tabButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tabId);
        });
        this.tabContents.forEach(content => {
            content.classList.toggle('active', content.id === `${tabId}Tab`);
        });

        // Always refresh the content when switching tabs
        if (tabId === 'courses') {
            this.loadAllCourses();
        } else if (tabId === 'programs') {
            this.loadPrograms();
        }
    }

    showNewProgramDialog() {
        document.getElementById('programId').value = '';
        document.getElementById('programDialogTitle').textContent = 'Създаване на нова програма';
        this.programBasicForm.reset();
        this.showDialog(this.programDialog);
    }

    showDialog(dialog) {
        dialog.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    hideDialog(dialog) {
        dialog.classList.add('hidden');
        document.body.style.overflow = '';
    }

    async loadPrograms() {
        try {
            const response = await fetch('api/programmes?page=1&limit=20');
            const data = await response.json();
            
            console.log('Programs tab data:', data); // Debug log
            
            this.programsContainer.innerHTML = '';
            
            if (!data || data.length === 0) {
                this.noProgramsMessage.style.display = 'flex';
                this.programsContainer.style.display = 'none';
            } else {
                this.noProgramsMessage.style.display = 'none';
                this.programsContainer.style.display = 'block';
                
                data.forEach(programme => {
                    console.log('Program in programs tab:', programme); // Debug log for each program
                    const programElement = this.createProgramElement(programme);
                    this.programsContainer.appendChild(programElement);
                });
            }
        } catch (error) {
            console.error('Error loading programs:', error);
            this.showValidationError('Възникна грешка при зареждане на програмите: ' + error.message);
            this.noProgramsMessage.style.display = 'flex';
            this.programsContainer.style.display = 'none';
        }
    }

    createProgramElement(programme) {
        const div = document.createElement('div');
        div.className = 'program-item';
        div.setAttribute('data-program-id', programme.id);
        div.innerHTML = `
            <div class="program-info" style="cursor: pointer;">
                <h3>${programme.name}</h3>
                <div class="program-metadata">
                    <span>Степен: ${programme.degree === 'bachelor' ? 'Бакалавър' : 'Магистър'}</span>
                    <span>Години: ${programme.years_to_study}</span>
                    <span>Вид: ${programme.type === 'full-time' ? 'Редовно' : programme.type === 'part-time' ? 'Задочно' : 'Дистанционно'}</span>
                </div>
            </div>
            <div class="program-actions">
                <button class="add-course-btn-small" title="Добави дисциплина">
                    <span>+</span> Добави дисциплина
                </button>
                <button class="edit-program">Редактирай</button>
                <button class="delete-program">Изтрий</button>
            </div>
        `;

        // Add click event to the program info section
        div.querySelector('.program-info').addEventListener('click', () => {
            this.navigateToProgramCourses(programme.id);
        });

        div.querySelector('.edit-program').addEventListener('click', () => this.editProgram(programme.id));
        div.querySelector('.delete-program').addEventListener('click', () => this.deleteProgram(programme.id));
        div.querySelector('.add-course-btn-small').addEventListener('click', () => this.showNewCourseDialog([programme]));
        
        return div;
    }

    navigateToProgramCourses(programId) {
        // Switch to courses tab
        this.switchTab('courses');
        
        // Find the program section in courses tab
        setTimeout(() => {
            const programSection = document.querySelector(`.program-courses-section[data-program-id="${programId}"]`);
            if (programSection) {
                // Scroll to the program section with smooth animation
                programSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                // Add highlight animation
                programSection.style.animation = 'highlight 1s ease';
            }
        }, 100); // Small delay to ensure DOM is updated after tab switch
    }

    async loadAllCourses() {
        try {
            const programsResponse = await fetch('api/programmes?page=1&limit=20');
            if (!programsResponse.ok) {
                throw new Error(`Failed to fetch programs: ${programsResponse.status}`);
            }
            const programs = await programsResponse.json();
            
            if (!programs || programs.length === 0) {
                this.allCoursesContainer.innerHTML = `
                    <div class="no-courses-message">
                        <div class="message-content">
                            <div class="message-icon">📚</div>
                            <h4>Няма създадени програми</h4>
                            <p>Първо създайте програма, за да добавите дисциплини към нея.</p>
                            <p>За да създадете програма:</p>
                            <ol>
                                <li>Отидете в таб "Програми"</li>
                                <li>Натиснете бутона "Нова програма"</li>
                                <li>Попълнете данните за програмата</li>
                            </ol>
                        </div>
                    </div>
                `;
                return;
            }

            const coursesContainer = document.createElement('div');
            coursesContainer.className = 'courses-container';
            this.allCoursesContainer.innerHTML = '';
            this.allCoursesContainer.appendChild(coursesContainer);

            const coursesResponse = await fetch('api/courses');
            if (!coursesResponse.ok) {
                throw new Error(`Failed to fetch courses: ${coursesResponse.status}`);
            }
            const data = await coursesResponse.json();
            const courses = Array.isArray(data) ? data : data.courses || [];

            const coursesByProgram = {};
            if (courses && courses.length > 0) {
                courses.forEach(course => {
                    if (!coursesByProgram[course.programme_id]) {
                        coursesByProgram[course.programme_id] = [];
                    }
                    coursesByProgram[course.programme_id].push(course);
                });
            }

            if (Object.keys(coursesByProgram).length === 0) {
                coursesContainer.innerHTML = `
                    <div class="no-courses-message">
                        <div class="message-content">
                            <div class="message-icon">📋</div>
                            <h4>Няма добавени дисциплини</h4>
                            <p>В момента няма добавени дисциплини в нито една програма.</p>
                            <p>За да добавите дисциплина:</p>
                            <ol>
                                <li>Изберете програма от списъка по-долу</li>
                                <li>Натиснете бутона "Добави" в секцията на програмата</li>
                                <li>Попълнете данните за дисциплината</li>
                            </ol>
                        </div>
                    </div>
                `;

                // Still show the programs even when there are no courses
                programs.forEach(program => {
                    const programSection = this.createProgramSection(program, coursesByProgram);
                    coursesContainer.appendChild(programSection);
                });
                return;
            }
            
            programs.forEach(program => {
                const programSection = this.createProgramSection(program, coursesByProgram);
                coursesContainer.appendChild(programSection);
            });

        } catch (error) {
            console.error('Error loading all courses:', error);
            this.allCoursesContainer.innerHTML = `
                <div class="error-message">
                    <div class="message-icon">⚠️</div>
                    <h3>Възникна грешка при зареждане на дисциплините</h3>
                    <p>Детайли за грешката: ${error.message}</p>
                    <p>Моля, опитайте отново по-късно или се свържете с администратор.</p>
                </div>
            `;
        }
    }

    createProgramSection(program, coursesByProgram) {
        const programSection = document.createElement('div');
        programSection.className = 'program-courses-section';
        programSection.setAttribute('data-program-id', program.id);
        
        // Add program header with metadata
        programSection.innerHTML = `
            <div class="program-header">
                <div class="program-info">
                    <h3>${program.name}</h3>
                    <div class="program-metadata">
                        <span>Степен: ${program.degree === 'bachelor' ? 'Бакалавър' : 'Магистър'}</span>
                        <span>Години: ${program.years_to_study}</span>
                        <span>Вид: ${program.type === 'full-time' ? 'Редовно' : program.type === 'part-time' ? 'Задочно' : 'Дистанционно'}</span>
                    </div>
                </div>
            </div>
        `;

        const programCourses = coursesByProgram[program.id] || [];
        if (programCourses.length > 0) {
            const coursesList = document.createElement('div');
            coursesList.className = 'courses-list';
            
            programCourses.forEach(course => {
                const courseItem = this.createCourseElement(course, program.id);
                coursesList.appendChild(courseItem);
            });
            
            programSection.appendChild(coursesList);
        } else {
            const noCourses = document.createElement('div');
            noCourses.className = 'no-courses-message';
            noCourses.innerHTML = `
                <div class="message-content">
                    <div class="message-icon">📚</div>
                    <h4>Няма добавени дисциплини</h4>
                    <p>В тази програма все още няма добавени дисциплини.</p>
                    <p>Натиснете бутона "Добави" за да добавите нова дисциплина.</p>
                </div>
            `;
            programSection.appendChild(noCourses);
        }

        // Add "Add new course" button
        const addCourseButton = document.createElement('div');
        addCourseButton.className = 'add-course-container';
        addCourseButton.innerHTML = `
            <button class="add-course-btn" data-program-id="${program.id}">
                <span>+</span> Добави
            </button>
        `;
        
        addCourseButton.querySelector('.add-course-btn').addEventListener('click', () => 
            this.showNewCourseDialog([program]));
            
        programSection.appendChild(addCourseButton);

        return programSection;
    }

    createCourseElement(course, programId) {
        const courseItem = document.createElement('div');
        courseItem.className = 'course-list-item';
        courseItem.setAttribute('data-course-id', course.id);

        // Safely handle dependencies display
        const dependenciesHtml = course.depends_on && Array.isArray(course.depends_on) && course.depends_on.length > 0
            ? `<span class="course-dependencies">Зависи от: ${course.depends_on.join(', ')}</span>`
            : '';

        courseItem.innerHTML = `
            <div class="course-info">
                <div class="course-header">
                    <span class="course-name">${course.name}</span>
                </div>
                <div class="course-details">
                    <span>ID: ${course.id}</span>
                    <span>Кредити: ${course.credits}</span>
                    <span>Година: ${course.year_available}</span>
                    ${dependenciesHtml}
                </div>
            </div>
            <div class="course-actions">
                <button class="edit-course" title="Редактирай" data-course-id="${course.id}" data-program-id="${programId}">Редактирай</button>
                <button class="delete-course-btn" title="Изтрий" data-course-id="${course.id}" data-program-id="${programId}">Изтрий</button>
            </div>
        `;

        // Add event listeners
        courseItem.querySelector('.edit-course').addEventListener('click', () => 
            this.editCourse(course.id, programId));
        courseItem.querySelector('.delete-course-btn').addEventListener('click', () => 
            this.deleteCourse(course.id, programId));

        return courseItem;
    }

    showNewCourseDialog(programs) {
        // Reset the form
        document.getElementById('newCourseForm').reset();
        
        // Populate programs dropdown
        const programSelect = document.getElementById('newCourseProgramme');
        programSelect.innerHTML = '<option value="">Изберете програма</option>';
        programs.forEach(program => {
            const option = document.createElement('option');
            option.value = program.id;
            option.textContent = program.name;
            programSelect.appendChild(option);
        });
        
        this.showDialog(this.newCourseDialog);
    }

    async editCourse(courseId, programId) {
        try {
            const response = await fetch(`api/programmes/${programId}/courses/${courseId}`);
            if (!response.ok) {
                throw new Error('Failed to fetch course details');
            }
            
            const course = await response.json();
            
            // Update form fields
            document.getElementById('courseId').value = courseId;
            document.getElementById('courseDialogTitle').textContent = 'Редактиране на дисциплина';
            document.getElementById('newCourseName').value = course.name || '';
            document.getElementById('newCourseCredits').value = course.credits || '';
            document.getElementById('newCourseYear').value = course.year_available || '';
            document.getElementById('newCourseProgramme').value = programId;
            document.getElementById('newCourseDependsOn').value = course.depends_on && course.depends_on.length > 0 ? course.depends_on[0] : '';
            
            // Disable program selection when editing
            document.getElementById('newCourseProgramme').disabled = true;
            
            this.showDialog(this.newCourseDialog);
        } catch (error) {
            console.error('Error loading course:', error);
            this.showMessage('Грешка при зареждане на дисциплината', 'error');
        }
    }

    async saveNewCourse(e) {
        e.preventDefault();
        
        try {
            const courseId = document.getElementById('courseId').value;
            const name = document.getElementById('newCourseName').value.trim();
            const credits = parseInt(document.getElementById('newCourseCredits').value);
            const yearAvailable = parseInt(document.getElementById('newCourseYear').value);
            const programId = document.getElementById('newCourseProgramme').value;
            const dependsOnInput = document.getElementById('newCourseDependsOn').value.trim();

            // Validate required fields
            if (!name) {
                throw new Error('Името на дисциплината е задължително');
            }
            if (isNaN(credits) || credits < 1 || credits > 9) {
                throw new Error('Кредитите трябва да са между 1 и 9');
            }
            if (!yearAvailable) {
                throw new Error('Изберете година на обучение');
            }
            if (!programId) {
                throw new Error('Изберете програма');
            }

            // Handle dependencies
            let dependsOn = [];
            if (dependsOnInput) {
                const dependencyId = parseInt(dependsOnInput);
                if (isNaN(dependencyId)) {
                    throw new Error('Невалиден ID на зависима дисциплина');
                }
                
                // Verify the dependency exists
                try {
                    const dependencyResponse = await fetch(`api/courses/${dependencyId}`);
                    if (!dependencyResponse.ok) {
                        throw new Error('Зависимата дисциплина не съществува');
                    }
                    dependsOn = [dependencyId];
                } catch (error) {
                    throw new Error('Зависимата дисциплина не съществува или е невалидна');
                }
            }

            const courseData = {
                name: name,
                credits: credits,
                year_available: yearAvailable,
                description: name,
                depends_on: dependsOn
            };

            // Determine if this is a create or update operation
            const method = courseId ? 'PUT' : 'POST';
            const url = courseId 
                ? `api/programmes/${programId}/courses/${courseId}`
                : `api/programmes/${programId}/courses`;

            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(courseData)
            });

            let responseData;
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                responseData = await response.json();
            }

            if (!response.ok) {
                throw new Error(responseData?.error || `Възникна грешка при ${courseId ? 'обновяване' : 'създаване'} на дисциплината`);
            }

            // Prepare the course data for UI update
            const courseForUI = {
                id: courseId || responseData?.id || responseData?.course_id,
                name: name,
                credits: credits,
                year_available: yearAvailable,
                depends_on: dependsOn
            };

            // Update UI immediately
            const programSection = document.querySelector(`.program-courses-section[data-program-id="${programId}"]`);
            if (programSection) {
                if (courseId) {
                    // Update existing course
                    const existingCourse = programSection.querySelector(`[data-course-id="${courseId}"]`);
                    if (existingCourse) {
                        const newCourseElement = this.createCourseElement(courseForUI, programId);
                        existingCourse.replaceWith(newCourseElement);
                    }
                } else {
                    // Add new course
                    let coursesList = programSection.querySelector('.courses-list');
                    const noCoursesMessage = programSection.querySelector('.no-courses-message');

                    if (!coursesList) {
                        coursesList = document.createElement('div');
                        coursesList.className = 'courses-list';
                        
                        const addCourseContainer = programSection.querySelector('.add-course-container');
                        if (addCourseContainer) {
                            programSection.insertBefore(coursesList, addCourseContainer);
                        } else {
                            programSection.appendChild(coursesList);
                        }
                    }

                    if (noCoursesMessage) {
                        noCoursesMessage.remove();
                    }

                    const courseElement = this.createCourseElement(courseForUI, programId);
                    coursesList.appendChild(courseElement);
                    courseElement.style.animation = 'fadeIn 0.3s ease-out';
                }

                // Only hide dialog and clear form if successful
                this.hideDialog(this.newCourseDialog);
                this.showMessage(`Дисциплината е ${courseId ? 'обновена' : 'добавена'} успешно`, 'success');
                document.getElementById('newCourseForm').reset();
                document.getElementById('courseId').value = '';
                document.getElementById('newCourseProgramme').disabled = false;
            } else {
                throw new Error('Грешка при обновяване на интерфейса');
            }

        } catch (error) {
            console.error('Error saving course:', error);
            this.showValidationError(error.message, this.newCourseDialog.querySelector('.modal-content'));
        }
    }

    async deleteCourse(courseId, programId) {
        if (!confirm('Сигурни ли сте, че искате да изтриете тази дисциплина?')) {
            return;
        }

        try {
            const response = await fetch(`api/programmes/${programId}/courses/${courseId}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Failed to delete course');
            }

            // Update UI immediately
            const courseElement = document.querySelector(`[data-course-id="${courseId}"]`);
            if (courseElement) {
                // Add fade-out animation
                courseElement.style.animation = 'fadeOut 0.3s ease-out';
                
                // Wait for animation to complete before removing
                setTimeout(() => {
                    courseElement.remove();
                    
                    // Check if this was the last course in the list
                    const programSection = document.querySelector(`.program-courses-section[data-program-id="${programId}"]`);
                    if (programSection) {
                        const coursesList = programSection.querySelector('.courses-list');
                        if (coursesList && !coursesList.children.length) {
                            // If no courses left, show the no courses message
                            coursesList.remove();
                            const noCoursesMessage = document.createElement('div');
                            noCoursesMessage.className = 'no-courses-message';
                            noCoursesMessage.innerHTML = `
                                <div class="message-content">
                                    <div class="message-icon">📚</div>
                                    <h4>Няма добавени дисциплини</h4>
                                    <p>В тази програма все още няма добавени дисциплини.</p>
                                    <p>Натиснете бутона "Добави" за да добавите нова дисциплина.</p>
                                </div>
                            `;
                            const addCourseContainer = programSection.querySelector('.add-course-container');
                            programSection.insertBefore(noCoursesMessage, addCourseContainer);
                        }
                    }
                }, 300);
            }

            this.showMessage('Дисциплината е изтрита успешно', 'success');
        } catch (error) {
            console.error('Error deleting course:', error);
            this.showMessage(error.message || 'Възникна грешка при изтриване на дисциплината', 'error');
        }
    }

    async saveProgram() {
        try {
            const programId = document.getElementById('programId').value;
            const programData = {
                name: document.getElementById('programName').value.trim(),
                type: document.getElementById('programType').value,
                degree: document.getElementById('educationDegree').value,
                years_to_study: parseInt(document.getElementById('yearsToStudy').value)
            };

            // Validate required fields
            if (!programData.name) {
                throw new Error('Името на програмата е задължително');
            }
            if (!programData.years_to_study || programData.years_to_study < 3 || programData.years_to_study > 6) {
                throw new Error('Годините на обучение трябва да са между 3 и 6');
            }
            if (!programData.type || !['full-time', 'part-time', 'distance'].includes(programData.type)) {
                throw new Error('Невалиден тип на обучение');
            }
            if (!programData.degree || !['bachelor', 'master'].includes(programData.degree)) {
                throw new Error('Невалидна образователна степен');
            }

            const method = programId ? 'PUT' : 'POST';
            const url = programId ? `api/programmes/${programId}` : 'api/programmes';

            const response = await fetch(url, {
                method,
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(programData)
            });

            let responseData;
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                responseData = await response.json();
            }

            if (!response.ok) {
                throw new Error(responseData?.error || `Възникна грешка при ${programId ? 'обновяване' : 'създаване'} на програмата`);
            }

            // Update UI immediately
            if (programId) {
                // Update existing program in programs tab
                const programItem = document.querySelector(`[data-program-id="${programId}"]`);
                if (programItem) {
                    const programInfo = programItem.querySelector('.program-info');
                    programInfo.innerHTML = `
                        <h3>${programData.name}</h3>
                        <div class="program-metadata">
                            <span>Степен: ${programData.degree === 'bachelor' ? 'Бакалавър' : 'Магистър'}</span>
                            <span>Години: ${programData.years_to_study}</span>
                            <span>Вид: ${programData.type === 'full-time' ? 'Редовно' : programData.type === 'part-time' ? 'Задочно' : 'Дистанционно'}</span>
                        </div>
                    `;
                }

                // Update program in courses tab
                const programSection = document.querySelector(`.program-courses-section[data-program-id="${programId}"]`);
                if (programSection) {
                    const programHeader = programSection.querySelector('.program-info');
                    programHeader.innerHTML = `
                        <h3>${programData.name}</h3>
                        <div class="program-metadata">
                            <span>Степен: ${programData.degree === 'bachelor' ? 'Бакалавър' : 'Магистър'}</span>
                            <span>Години: ${programData.years_to_study}</span>
                            <span>Вид: ${programData.type === 'full-time' ? 'Редовно' : programData.type === 'part-time' ? 'Задочно' : 'Дистанционно'}</span>
                        </div>
                    `;
                }
            }

            // Only hide dialog and clear form if successful
            this.hideDialog(this.programDialog);
            this.showMessage(`Програмата е ${programId ? 'обновена' : 'създадена'} успешно`, 'success');
            this.programBasicForm.reset();
            document.getElementById('programId').value = '';

            // Refresh both views to ensure consistency
            await Promise.all([
                this.loadPrograms(),
                this.loadAllCourses()
            ]);
        } catch (error) {
            console.error('Error saving program:', error);
            this.showValidationError(error.message, this.programDialog.querySelector('.modal-content'));
            // Don't hide dialog or clear form on error
        }
    }

    showMessage(message, type = 'success') {
        const messageContainer = document.createElement('div');
        messageContainer.className = `message ${type}-message`;
        messageContainer.textContent = message;
        
        const icon = document.createElement('span');
        icon.textContent = type === 'success' ? '✓' : '⚠';
        messageContainer.insertBefore(icon, messageContainer.firstChild);
        
        // Remove any existing messages
        const existingMessages = document.querySelectorAll('.message');
        existingMessages.forEach(msg => msg.remove());
        
        document.querySelector('.container').insertBefore(messageContainer, document.querySelector('.programs-list'));
        
        setTimeout(() => messageContainer.remove(), 3000);
    }

    showValidationError(message, targetElement = null) {
        // Remove any existing validation errors first
        const existingErrors = document.querySelectorAll('.validation-error');
        existingErrors.forEach(err => err.remove());

        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error';
        errorDiv.textContent = message;
        
        if (targetElement) {
            targetElement.insertBefore(errorDiv, targetElement.firstChild);
        } else {
            const modalContent = document.querySelector('.modal-content');
            if (modalContent && modalContent.isConnected) {
                modalContent.insertBefore(errorDiv, modalContent.firstChild);
            } else {
                // If no modal is open, show as a general message
                this.showMessage(message, 'error');
                return;
            }
        }
    }

    async deleteProgram(id) {
        if (!confirm('Сигурни ли сте, че искате да изтриете тази програма? Всички дисциплини в нея ще бъдат изтрити.')) {
            return;
        }

        try {
            const response = await fetch(`api/programmes/${id}`, { 
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Грешка при изтриване на програмата');
            }

            // Update UI immediately in programs tab
            const programItem = document.querySelector(`.program-item[data-program-id="${id}"]`);
            if (programItem) {
                programItem.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    programItem.remove();
                    
                    // Check if this was the last program
                    const programsContainer = document.getElementById('programsContainer');
                    if (!programsContainer.children.length) {
                        const noProgramsMessage = document.getElementById('noProgramsMessage');
                        if (noProgramsMessage) {
                            noProgramsMessage.style.display = 'flex';
                        }
                        programsContainer.style.display = 'none';
                    }
                }, 300);
            }

            // Update UI immediately in courses tab
            const programSection = document.querySelector(`.program-courses-section[data-program-id="${id}"]`);
            if (programSection) {
                programSection.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    programSection.remove();
                    
                    // Check if this was the last program in courses tab
                    const coursesContainer = document.querySelector('.courses-container');
                    if (coursesContainer && !coursesContainer.children.length) {
                        coursesContainer.innerHTML = `
                            <div class="no-courses-message">
                                <div class="message-content">
                                    <div class="message-icon">📚</div>
                                    <h4>Няма създадени програми</h4>
                                    <p>Първо създайте програма, за да добавите дисциплини към нея.</p>
                                    <p>За да създадете програма:</p>
                                    <ol>
                                        <li>Отидете в таб "Програми"</li>
                                        <li>Натиснете бутона "Нова програма"</li>
                                        <li>Попълнете данните за програмата</li>
                                    </ol>
                                </div>
                            </div>
                        `;
                    }
                }, 300);
            }

            this.showMessage('Програмата е изтрита успешно', 'success');
        } catch (error) {
            console.error('Error deleting program:', error);
            this.showMessage(error.message || 'Възникна грешка при изтриване на програмата', 'error');
        }
    }

    async editProgram(id) {
        try {
            const response = await fetch(`api/programmes/${id}`);
            if (!response.ok) {
                throw new Error('Failed to fetch program details');
            }
            
            const programme = await response.json();
            
            // Update form fields
            document.getElementById('programId').value = id;
            document.getElementById('programDialogTitle').textContent = 'Редактиране на програма';
            document.getElementById('programName').value = programme.name || '';
            document.getElementById('educationDegree').value = programme.degree || 'bachelor';
            document.getElementById('yearsToStudy').value = programme.years_to_study || '4';
            document.getElementById('programType').value = programme.type || 'full-time';
            
            this.showDialog(this.programDialog);
        } catch (error) {
            console.error('Error loading program:', error);
            this.showMessage('Грешка при зареждане на програмата', 'error');
        }
    }
}

// Initialize the application when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.programEditor = new ProgramEditor();
}); 