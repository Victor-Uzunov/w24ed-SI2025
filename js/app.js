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
        this.addCourseBtn = document.getElementById('addCourseBtn');
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
                        <h2>Добавяне на нова дисциплина</h2>
                        <button class="close-modal" id="closeNewCourseDialog">×</button>
                    </div>
                    <form id="newCourseForm">
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
                            <label for="newCourseDependsOn">Зависи от (ID на друга дисциплина)</label>
                            <input type="number" id="newCourseDependsOn" min="1">
                        </div>
                        <div class="button-group">
                            <button type="button" class="cancel-btn" id="cancelNewCourse">Отказ</button>
                            <button type="submit" class="save-btn">Създай дисциплина</button>
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
        this.coursesList = document.getElementById('coursesList');
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
            
            this.programsContainer.innerHTML = '';
            
            if (!data || data.length === 0) {
                this.noProgramsMessage.style.display = 'flex';
                this.programsContainer.style.display = 'none';
            } else {
                this.noProgramsMessage.style.display = 'none';
                this.programsContainer.style.display = 'block';
                
                data.forEach(programme => {
                    const div = document.createElement('div');
                    div.className = 'program-item';
                    div.style.cursor = 'pointer';
                    div.innerHTML = `
                        <div class="program-info">
                            <h3>${programme.name}</h3>
                            <p>${programme.years_to_study} години, ${programme.type}</p>
                        </div>
                        <div class="program-actions">
                            <button class="edit-program">Редактирай</button>
                            <button class="delete-program">Изтрий</button>
                        </div>
                    `;

                    // Add click handler for the entire program item
                    div.addEventListener('click', (e) => {
                        // Prevent triggering when clicking buttons
                        if (!e.target.closest('.program-actions')) {
                            this.manageCourses(programme.id, programme.name);
                        }
                    });

                    div.querySelector('.edit-program').addEventListener('click', () => this.editProgram(programme.id));
                    div.querySelector('.delete-program').addEventListener('click', () => this.deleteProgram(programme.id));
                    
                    this.programsContainer.appendChild(div);
                });
            }
        } catch (error) {
            console.error('Error loading programs:', error);
            this.showValidationError('Възникна грешка при зареждане на програмите: ' + error.message);
            this.noProgramsMessage.style.display = 'flex';
            this.programsContainer.style.display = 'none';
        }
    }

    async loadAllCourses() {
        try {
            // First get all programs
            const programsResponse = await fetch('api/programmes?page=1&limit=20');
            if (!programsResponse.ok) {
                throw new Error(`Failed to fetch programs: ${programsResponse.status}`);
            }
            const programs = await programsResponse.json();
            
            if (!programs || programs.length === 0) {
                this.allCoursesContainer.innerHTML = `
                    <div class="no-courses-message">
                        <div class="message-icon">📚</div>
                        <h3>Няма създадени програми</h3>
                        <p>Първо създайте програма, за да добавите дисциплини към нея.</p>
                        <p>За да създадете програма:</p>
                        <ol>
                            <li>Отидете в таб "Програми"</li>
                            <li>Натиснете бутона "Нова програма"</li>
                            <li>Попълнете данните за програмата</li>
                        </ol>
                    </div>
                `;
                return;
            }

            // Then get all courses
            const coursesResponse = await fetch('api/courses');
            if (!coursesResponse.ok) {
                console.error('Error response from courses API:', await coursesResponse.text());
                throw new Error(`Failed to fetch courses: ${coursesResponse.status}`);
            }
            const data = await coursesResponse.json();
            const courses = Array.isArray(data) ? data : data.courses || [];

            // Check if there are any courses at all
            let totalCourses = 0;
            const coursesByProgram = {};
            
            if (courses && courses.length > 0) {
                courses.forEach(course => {
                    if (!coursesByProgram[course.programme_id]) {
                        coursesByProgram[course.programme_id] = [];
                    }
                    coursesByProgram[course.programme_id].push(course);
                    totalCourses++;
                });
            }

            if (totalCourses === 0) {
                this.allCoursesContainer.innerHTML = `
                    <div class="no-courses-message">
                        <div class="message-icon">📚</div>
                        <h3>Няма добавени дисциплини</h3>
                        <p>В момента няма добавени дисциплини в нито една програма.</p>
                        <p>За да добавите дисциплини:</p>
                        <ol>
                            <li>Отидете в таб "Програми"</li>
                            <li>Изберете програма</li>
                            <li>Натиснете бутона "+" за добавяне на нова дисциплина</li>
                        </ol>
                    </div>
                `;
                return;
            }

            this.allCoursesContainer.innerHTML = '';
            
            // Create sections for each program
            programs.forEach(program => {
                const programCourses = coursesByProgram[program.id] || [];
                const programSection = document.createElement('div');
                programSection.className = 'program-courses-section';
                
                // Add program header
                programSection.innerHTML = `
                    <div class="program-header">
                        <h3>${program.name}</h3>
                    </div>
                `;

                if (programCourses.length === 0) {
                    // Show no courses message for this program
                    programSection.innerHTML += `
                        <div class="no-courses-message">
                            <p>Няма добавени дисциплини за тази програма.</p>
                        </div>
                    `;
                } else {
                    // Add courses list
                    const coursesList = document.createElement('div');
                    coursesList.className = 'courses-list';
                    
                    programCourses.forEach(course => {
                        const courseItem = document.createElement('div');
                        courseItem.className = 'course-list-item';
                        courseItem.innerHTML = `
                            <div class="course-id">ID: ${course.id}</div>
                            <div class="course-name">${course.name}</div>
                            <div class="course-credits">${course.credits} кредита</div>
                            <div class="course-year">Година ${course.year_available}</div>
                        `;
                        coursesList.appendChild(courseItem);
                    });
                    
                    programSection.appendChild(coursesList);
                }

                this.allCoursesContainer.appendChild(programSection);
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

    async saveProgram() {
        try {
            const programId = document.getElementById('programId').value;
            const programData = {
                name: document.getElementById('programName').value,
                type: document.getElementById('programType').value,
                degree: document.getElementById('educationDegree').value,
                years_to_study: parseInt(document.getElementById('yearsToStudy').value)
            };

            const method = programId ? 'PUT' : 'POST';
            const url = programId ? `api/programmes/${programId}` : 'api/programmes';

            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(programData)
            });

            if (response.ok) {
                const newProgramData = await response.json();
                this.hideDialog(this.programDialog);
                this.showMessage(`Програмата е ${programId ? 'обновена' : 'създадена'} успешно`, 'success');
                
                // Refresh both program list and courses tab
                await Promise.all([
                    this.loadPrograms(),
                    this.loadAllCourses()
                ]);
                
                // If this was a new program, open the course management dialog
                if (!programId && newProgramData.id) {
                    await this.manageCourses(newProgramData.id, programData.name);
                }
            } else {
                const data = await response.json();
                this.showValidationError(data.message || `Възникна грешка при ${programId ? 'обновяване' : 'създаване'} на програмата`);
            }
        } catch (error) {
            console.error('Error saving program:', error);
            this.showValidationError('Възникна неочаквана грешка');
        }
    }

    async manageCourses(programId, programName) {
        this.currentProgramId = programId;
        
        // Hide programs tab content and show courses content
        document.getElementById('programsTab').classList.remove('active');
        document.getElementById('coursesTab').classList.add('active');
        
        // Update courses tab content
        const coursesTab = document.getElementById('coursesTab');
        coursesTab.innerHTML = `
            <div class="courses-page">
                <div class="program-courses-section">
                    <div class="program-header">
                        <h3>${programName}</h3>
                        <div class="program-actions">
                            <button id="addCourseBtn" class="add-course-btn">
                                <span>+</span> Добави нова дисциплина
                            </button>
                            <button id="backToProgramsBtn" class="back-btn">← Обратно към програмите</button>
                        </div>
                    </div>
                    <div id="programCoursesList" class="courses-list"></div>
                </div>
            </div>
        `;

        // Add event listeners
        document.getElementById('addCourseBtn').addEventListener('click', () => this.addCourse());
        document.getElementById('backToProgramsBtn').addEventListener('click', () => {
            this.showProgramsTab();
            // Update active tab button
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.tab === 'programs') {
                    btn.classList.add('active');
                }
            });
        });
        
        try {
            const response = await fetch(`api/programmes/${programId}/courses`);
            if (!response.ok) {
                throw new Error('Failed to fetch courses');
            }
            const data = await response.json();
            const courses = Array.isArray(data) ? data : data.courses || [];
            
            const coursesList = document.getElementById('programCoursesList');
            if (courses.length === 0) {
                coursesList.innerHTML = `
                    <div class="no-courses-message">
                        <div class="message-icon">📚</div>
                        <h3>Няма добавени дисциплини</h3>
                        <p>Използвайте бутона "+" за да добавите нова дисциплина към тази програма.</p>
                    </div>
                `;
            } else {
                coursesList.innerHTML = '';
                courses.forEach(course => {
                    const courseItem = document.createElement('div');
                    courseItem.className = 'course-list-item';
                    courseItem.innerHTML = `
                        <div class="course-info">
                            <div class="course-id">ID: ${course.id}</div>
                            <div class="course-name">${course.name}</div>
                            <div class="course-credits">${course.credits} кредита</div>
                            <div class="course-year">Година ${course.year_available}</div>
                            ${course.depends_on && course.depends_on.length > 0 ? 
                                `<div class="course-dependencies">Зависи от: ${course.depends_on.join(', ')}</div>` : 
                                ''}
                        </div>
                        <div class="course-actions">
                            <button class="edit-course-btn" title="Редактирай">✎</button>
                            <button class="delete-course-btn" title="Изтрий">✕</button>
                        </div>
                    `;

                    // Add event listeners for edit and delete buttons
                    courseItem.querySelector('.edit-course-btn').addEventListener('click', () => 
                        this.editCourse(course.id, course));
                    courseItem.querySelector('.delete-course-btn').addEventListener('click', () => 
                        this.deleteCourse(course.id));

                    coursesList.appendChild(courseItem);
                });
            }
        } catch (error) {
            console.error('Error loading courses:', error);
            document.getElementById('programCoursesList').innerHTML = `
                <div class="error-message">
                    <div class="message-icon">⚠️</div>
                    <h3>Възникна грешка при зареждане на дисциплините</h3>
                    <p>Моля, опитайте отново по-късно.</p>
                </div>
            `;
        }
    }

    async editCourse(courseId, courseData) {
        // Show the course dialog with pre-filled data
        this.showDialog(this.newCourseDialog);
        
        // Fill the form with existing course data
        document.getElementById('newCourseName').value = courseData.name || '';
        document.getElementById('newCourseCredits').value = courseData.credits || '';
        document.getElementById('newCourseYear').value = courseData.year_available || '';
        document.getElementById('newCourseDependsOn').value = 
            courseData.depends_on && courseData.depends_on.length > 0 ? 
            courseData.depends_on[0] : '';
        
        // Update dialog title
        this.newCourseDialog.querySelector('h2').textContent = 'Редактиране на дисциплина';
        
        // Update form submission handler
        const form = document.getElementById('newCourseForm');
        const originalSubmitHandler = form.onsubmit;
        form.onsubmit = async (e) => {
            e.preventDefault();
            try {
                const updatedData = {
                    name: document.getElementById('newCourseName').value.trim(),
                    credits: parseInt(document.getElementById('newCourseCredits').value),
                    year_available: parseInt(document.getElementById('newCourseYear').value),
                    description: document.getElementById('newCourseName').value.trim(),
                    depends_on: document.getElementById('newCourseDependsOn').value.trim() ? 
                        [parseInt(document.getElementById('newCourseDependsOn').value)] : []
                };

                const response = await fetch(`api/programmes/${this.currentProgramId}/courses/${courseId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updatedData)
                });

                if (!response.ok) {
                    throw new Error('Failed to update course');
                }

                this.hideDialog(this.newCourseDialog);
                this.showMessage('Дисциплината е обновена успешно', 'success');
                
                // Refresh the view
                await this.manageCourses(this.currentProgramId, 
                    document.querySelector('.courses-header h2').textContent.split(': ')[1]);
            } catch (error) {
                console.error('Error updating course:', error);
                this.showValidationError('Възникна грешка при обновяване на дисциплината');
            }
        };
    }

    async deleteCourse(courseId) {
        if (!confirm('Сигурни ли сте, че искате да изтриете тази дисциплина?')) {
            return;
        }

        try {
            const response = await fetch(`api/programmes/${this.currentProgramId}/courses/${courseId}`, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error('Failed to delete course');
            }

            this.showMessage('Дисциплината е изтрита успешно', 'success');
            
            // Refresh both views
            await Promise.all([
                this.manageCourses(this.currentProgramId, 
                    document.querySelector('.courses-header h2').textContent.split(': ')[1]),
                this.loadAllCourses()
            ]);
        } catch (error) {
            console.error('Error deleting course:', error);
            this.showValidationError('Възникна грешка при изтриване на дисциплината');
        }
    }

    showProgramsTab() {
        // Show programs tab and hide courses tab
        document.getElementById('programsTab').classList.add('active');
        document.getElementById('coursesTab').classList.remove('active');
        
        // Update active tab button
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.tab === 'programs') {
                btn.classList.add('active');
            }
        });
        
        // Refresh programs list
        this.loadPrograms();
    }

    async saveCourseChanges() {
        if (!this.currentProgramId) {
            this.showValidationError('Невалидна програма');
            return;
        }

        try {
            const courseElements = document.querySelectorAll('.course-item');
            const courses = Array.from(courseElements).map(item => {
                const name = item.querySelector('.course-name').value.trim();
                const credits = parseInt(item.querySelector('.course-credits').value);
                const year = parseInt(item.querySelector('.course-year').value);
                const dependsOnValue = item.querySelector('.depends-on-id').value.trim();

                // Validate required fields
                if (!name) {
                    throw new Error('Името на дисциплината е задължително');
                }
                if (isNaN(credits) || credits < 1 || credits > 9) {
                    throw new Error('Кредитите трябва да са между 1 и 9');
                }
                if (!year) {
                    throw new Error('Изберете година на обучение');
                }

                return {
                    id: parseInt(item.dataset.courseId),
                    name: name,
                    credits: credits,
                    year_available: year,
                    depends_on: dependsOnValue ? parseInt(dependsOnValue) : null,
                    programme_id: this.currentProgramId
                };
            });

            const response = await fetch(`api/programmes/${this.currentProgramId}/courses`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ courses })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Възникна грешка при запазване на дисциплините');
            }

            this.showMessage('Дисциплините са запазени успешно', 'success');
            
            // Refresh the courses list
            await this.manageCourses(this.currentProgramId, document.querySelector('.courses-header h2').textContent.split(': ')[1]);
        } catch (error) {
            console.error('Error saving courses:', error);
            this.showValidationError(error.message || 'Възникна неочаквана грешка при запазване на дисциплините');
        }
    }

    addCourse() {
        // Show the new course dialog
        this.showDialog(this.newCourseDialog);
        
        // Reset form and clear all fields
        document.getElementById('newCourseForm').reset();
    }

    async saveNewCourse(e) {
        e.preventDefault();
        
        try {
            const name = document.getElementById('newCourseName').value.trim();
            const credits = parseInt(document.getElementById('newCourseCredits').value);
            const yearAvailable = parseInt(document.getElementById('newCourseYear').value);
            const dependsOn = document.getElementById('newCourseDependsOn').value.trim();

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

            const courseData = {
                name: name,
                credits: credits,
                year_available: yearAvailable,
                description: name, // Using name as description for now
                depends_on: dependsOn ? [parseInt(dependsOn)] : [] // API expects an array
            };

            // Send request to create the course
            const response = await fetch(`api/programmes/${this.currentProgramId}/courses`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(courseData)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Възникна грешка при създаване на дисциплината');
            }

            // Hide the dialog
            this.hideDialog(this.newCourseDialog);
            
            // Show success message
            this.showMessage('Дисциплината е добавена успешно', 'success');

            // Refresh both the courses view and all courses tab
            await Promise.all([
                this.manageCourses(this.currentProgramId, document.querySelector('.courses-header h2').textContent.split(': ')[1]),
                this.loadAllCourses()
            ]);
        } catch (error) {
            console.error('Error creating course:', error);
            this.showValidationError(error.message || 'Възникна грешка при създаване на дисциплината');
        }
    }

    addCourseToUI(courseData) {
        const courseElement = this.courseTemplate.content.cloneNode(true);
        const courseItem = courseElement.querySelector('.course-item');
        
        courseItem.dataset.courseId = courseData.id;
        courseItem.innerHTML = `
            <div class="course-header">
                <span class="course-id">ID: <span class="course-id-value">${courseData.id}</span></span>
            </div>
            <div class="course-content">
                <div class="form-group">
                    <label>Име на дисциплината</label>
                    <input type="text" class="course-name" value="${courseData.name || ''}" required>
                </div>
                <div class="form-group">
                    <label>Кредити</label>
                    <input type="number" class="course-credits" value="${courseData.credits || 1}" min="1" max="9" required>
                </div>
                <div class="form-group">
                    <label>Година</label>
                    <select class="course-year" required>
                        ${[1, 2, 3, 4, 5].map(year => 
                            `<option value="${year}" ${(courseData.year_available || 1) === year ? 'selected' : ''}>
                                ${year}-ва година
                            </option>`
                        ).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Зависи от (ID на друга дисциплина)</label>
                    <input type="number" class="depends-on-id" value="${courseData.depends_on || ''}" min="1">
                </div>
            </div>
            <div class="course-actions">
                <button class="remove-course" title="Изтрий дисциплината">✕</button>
            </div>
        `;
        
        // Add validation for credits
        const creditsInput = courseItem.querySelector('.course-credits');
        creditsInput.addEventListener('input', (e) => {
            const value = parseInt(e.target.value);
            if (value < 1 || value > 9) {
                creditsInput.setCustomValidity('Кредитите трябва да са между 1 и 9');
            } else {
                creditsInput.setCustomValidity('');
            }
            creditsInput.reportValidity();
        });

        // Add validation for course name
        const nameInput = courseItem.querySelector('.course-name');
        nameInput.addEventListener('input', (e) => {
            if (e.target.value.trim() === '') {
                nameInput.setCustomValidity('Името на дисциплината е задължително');
            } else {
                nameInput.setCustomValidity('');
            }
            nameInput.reportValidity();
        });
        
        // Add remove course handler
        courseItem.querySelector('.remove-course').addEventListener('click', (e) => {
            const courseElement = e.target.closest('.course-item');
            courseElement.remove();
        });

        this.coursesList.appendChild(courseItem);
    }

    showMessage(message, type = 'success') {
        const messageContainer = document.createElement('div');
        messageContainer.className = `${type}-message`;
        messageContainer.textContent = message;
        
        const icon = document.createElement('span');
        icon.textContent = type === 'success' ? '✓' : '⚠';
        messageContainer.insertBefore(icon, messageContainer.firstChild);
        
        document.querySelector('.container').insertBefore(messageContainer, document.querySelector('.programs-list'));
        
        setTimeout(() => messageContainer.remove(), 3000);
    }

    showValidationError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error';
        errorDiv.textContent = message;
        
        const targetContainer = this.courseDialog.classList.contains('hidden') ? 
            this.programDialog.querySelector('.modal-content') : 
            this.courseDialog.querySelector('.modal-content');
        
        targetContainer.insertBefore(errorDiv, targetContainer.firstChild);
        setTimeout(() => errorDiv.remove(), 3000);
    }

    async deleteProgram(id) {
        try {
            await fetch(`api/programmes/${id}`, { method: 'DELETE' });
            this.showMessage('Програмата е изтрита успешно', 'success');
            
            // Refresh both program list and courses tab
            await Promise.all([
                this.loadPrograms(),
                this.loadAllCourses()
            ]);
        } catch (error) {
            this.showValidationError('Грешка при изтриване на програмата');
        }
    }

    async editProgram(id) {
        try {
            const response = await fetch(`api/programmes/${id}`);
            const programme = await response.json();
            
            document.getElementById('programId').value = id;
            document.getElementById('programDialogTitle').textContent = 'Редактиране на програма';
            document.getElementById('programName').value = programme.name || '';
            document.getElementById('educationDegree').value = programme.degree || 'bachelor';
            document.getElementById('yearsToStudy').value = programme.years_to_study || '4';
            document.getElementById('programType').value = programme.type || 'full-time';
            
            this.showDialog(this.programDialog);
        } catch (error) {
            console.error('Error loading program:', error);
            this.showValidationError('Грешка при зареждане на програмата');
        }
    }
}

// Initialize the application when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.programEditor = new ProgramEditor();
}); 