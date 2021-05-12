import config from '../../config';
import axios from 'axios';
import User from "../User/user.js"

export default class Api {
    constructor() {
        this.baseUrl = config.host + '/api';
    }

    post(url, postData, successCallback=null, errorCallback=null) {
        let config = null;

        let user = new User;
        if (user.hasAuthToken()) {
            let config = {
                headers: {
                    'X-AUTH-TOKEN': user.getAuthToken()
                }
            };
        }

        axios.post(this.baseUrl + url, postData, config)
            .then(
                response => (successCallback(response.data)),
                error => (errorCallback(error.response.data))
            );
    }
}