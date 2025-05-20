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
            <button id="loadProgramBtn">Зареди програма</button>
            <button id="saveProgramBtn">Запази</button>
        </nav>
    </header>

    <main>
        <div class="container">
            <div id="programEditor">
                <section id="basicInfo">
                    <h2>Основна информация</h2>
                    <form id="programBasicForm">
                        <div class="form-group">
                            <label for="programName">Име на програмата:</label>
                            <input type="text" id="programName" required>
                        </div>
                        <div class="form-group">
                            <label for="programType">Вид на програмата:</label>
                            <select id="programType">
                                <option value="bachelor">Бакалавър</option>
                                <option value="master">Магистър</option>
                            </select>
                        </div>
                    </form>
                </section>

                <section id="coursesSection">
                    <h2>Дисциплини</h2>
                    <div id="coursesList"></div>
                    <button id="addCourseBtn">Добави дисциплина</button>
                </section>

                <section id="dependenciesSection">
                    <h2>Зависимости между дисциплините</h2>
                    <div id="dependencyGraph"></div>
                </section>
            </div>
        </div>
    </main>

    <!-- Templates -->
    <template id="courseTemplate">
        <div class="course-item">
            <input type="text" class="course-name" placeholder="Име на дисциплината">
            <select class="course-semester">
                <option value="1">Семестър 1</option>
                <option value="2">Семестър 2</option>
                <option value="3">Семестър 3</option>
                <option value="4">Семестър 4</option>
                <option value="5">Семестър 5</option>
                <option value="6">Семестър 6</option>
                <option value="7">Семестър 7</option>
                <option value="8">Семестър 8</option>
            </select>
            <input type="number" class="course-credits" placeholder="Кредити" min="0">
            <select class="course-type">
                <option value="mandatory">Задължителна</option>
                <option value="optional">Избираема</option>
                <option value="facultative">Факултативна</option>
            </select>
            <button class="remove-course">Премахни</button>
        </div>
    </template>

    <script src="js/app.js"></script>
    <script src="js/graph.js"></script>
</body>
</html> 