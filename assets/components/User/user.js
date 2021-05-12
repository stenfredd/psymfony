export default class User {
    setAuthToken(token) {
        localStorage.setItem('authToken', token);
    }

    getAuthToken() {
        localStorage.getItem('authToken');
    }

    hasAuthToken() {
        let authToken = localStorage.getItem('authToken');

        if (authToken !== null && authToken !== '') {
            return true;
        }
        return false;
    }

    deleteToken() {
        localStorage.removeItem('authToken');
    }

}