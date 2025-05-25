class Api {
    static async request(endpoint, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json'
            }
        };

        const response = await fetch(endpoint, { ...defaultOptions, ...options });
        const data = await response.json();

        if (!response.ok) {
            throw {
                status: response.status,
                ...data
            };
        }

        return data;
    }

    static async createProgramme(data) {
        return this.request('/api/programmes.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async getProgrammes(page = 1, perPage = 10) {
        return this.request(`/api/programmes.php?page=${page}&per_page=${perPage}`);
    }

    static async getProgramme(id) {
        return this.request(`/api/programmes.php?id=${id}`);
    }

    static async deleteProgramme(id) {
        return this.request(`/api/programmes.php?id=${id}`, {
            method: 'DELETE'
        });
    }

    static async createCourse(programmeId, data) {
        return this.request(`/api/courses.php?programme_id=${programmeId}`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async getCourse(programmeId, courseId) {
        return this.request(`/api/courses.php?programme_id=${programmeId}&id=${courseId}`);
    }

    static async deleteCourse(programmeId, courseId) {
        return this.request(`/api/courses.php?programme_id=${programmeId}&id=${courseId}`, {
            method: 'DELETE'
        });
    }
} 