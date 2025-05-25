<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ФМИ - Редактор на учебни програми</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Редактор на учебни програми - ФМИ</h1>
        <nav>
            <button id="newProgramBtn">Нова програма</button>
        </nav>
    </header>

    <main>
        <div class="container">
            <!-- Tabs Navigation -->
            <div class="tabs">
                <button class="tab-btn active" data-tab="programs">Програми</button>
                <button class="tab-btn" data-tab="courses">Всички дисциплини</button>
            </div>

            <!-- Programs Tab -->
            <div id="programsTab" class="tab-content active">
                <div id="programsList" class="programs-list">
                    <div id="noProgramsMessage" class="no-programs-message">
                        <div class="message-content">
                            <h2>Няма налични програми</h2>
                            <p>Натиснете бутона "Нова програма", за да създадете нова учебна програма.</p>
                        </div>
                    </div>
                    <div id="programsContainer">
                        <!-- Programs will be listed here -->
                    </div>
                </div>
            </div>

            <!-- Courses Tab -->
            <div id="coursesTab" class="tab-content">
                <div class="courses-list">
                    <div id="noCoursesMessage" class="no-courses-message">
                        <div class="message-content">
                            <h2>Няма налични дисциплини</h2>
                            <p>Добавете дисциплини към някоя от програмите.</p>
                        </div>
                    </div>
                    <div id="allCoursesContainer">
                        <!-- All courses will be listed here -->
                    </div>
                </div>
            </div>

            <!-- Program Creation/Edit Dialog -->
            <div id="programDialog" class="modal hidden">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="programDialogTitle">Създаване на нова програма</h2>
                        <button class="close-modal" id="closeDialog">&times;</button>
                    </div>
                    <form id="programBasicForm">
                        <input type="hidden" id="programId">
                        <div class="form-group">
                            <label for="programName">Име на програмата:</label>
                            <input type="text" id="programName" required>
                        </div>
                        <div class="form-group">
                            <label for="educationDegree">Степен на обучение:</label>
                            <select id="educationDegree" required>
                                <option value="bachelor">Бакалавър</option>
                                <option value="master">Магистър</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="yearsToStudy">Години на обучение:</label>
                            <select id="yearsToStudy">
                                <option value="3">3 години</option>
                                <option value="4">4 години</option>
                                <option value="5">5 години</option>
                                <option value="6">6 години</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="programType">Вид на програмата:</label>
                            <select id="programType">
                                <option value="full-time">Редовно</option>
                                <option value="part-time">Задочно</option>
                                <option value="distance">Дистанционно</option>
                            </select>
                        </div>
                        <div class="button-group">
                            <button type="submit" id="saveProgramBtn">Запази</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Course Management Dialog -->
            <div id="courseDialog" class="modal hidden">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Управление на дисциплини</h2>
                        <span class="program-name-display"></span>
                        <button class="close-modal" id="closeCourseDialog">&times;</button>
                    </div>
                    <div id="coursesList"></div>
                    <button id="addCourseBtn" class="add-course-btn">Добави дисциплина</button>
                    <div class="button-group">
                        <button type="button" id="saveCourseChangesBtn">Запази промените</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Course Template -->
    <template id="courseTemplate">
        <div class="course-item">
            <div class="course-id">ID: <span class="course-id-value"></span></div>
            <input type="text" class="course-name" placeholder="Име на дисциплината" required>
            <input type="number" class="course-credits" placeholder="Кредити" min="1" max="9" required>
            <select class="course-year" required>
                <option value="">Изберете година</option>
                <option value="1">Година 1</option>
                <option value="2">Година 2</option>
                <option value="3">Година 3</option>
                <option value="4">Година 4</option>
                <option value="5">Година 5</option>
                <option value="6">Година 6</option>
            </select>
            <input type="number" class="depends-on-id" placeholder="ID на зависима дисциплина (незадължително)">
            <button class="remove-course">Премахни</button>
        </div>
    </template>

    <script src="js/app.js"></script>
</body>
</html> 