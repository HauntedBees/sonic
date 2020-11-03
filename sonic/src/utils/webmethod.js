import store from "src/main";
class Beeliever {
    constructor(path, withCredentials) {
        this.path = path;
        this.withCredentials = withCredentials || false;
    }
    getHeaders() { return this.withCredentials ? { "Authorization": "Bearer " + store.state.token } : undefined; }
    handleResponse(response) {
        if(!response.ok) { // TODO: actually show response messages if they exist
            if(response.status === 401) {
                throw new Error("Access denied.");
            } else {
                throw new Error("Something ain't right.");
            }
        }
        return response.json();
    }
    auth(successCallback, errorCallback) {
        fetch(this.path + "Auth/", {
            method: "GET",
            headers: this.getHeaders(),
            credentials: this.withCredentials ? "same-origin" : "omit"
        }).then(this.handleResponse).then(data => {
            successCallback(data);
        }).catch(error => {
            store.state.auth = false;
            errorCallback(error);
        });
    }
    get(path, param, successCallback, failCallback, errorCallback, forceCredentials) {
        if(store) { store.commit("startLoad"); }
        let paramStr = !param ? "/" : ("/" + encodeURIComponent(JSON.stringify(param)));
        fetch(this.path + path + paramStr, {
            method: "GET",
            headers: this.getHeaders(),
            credentials: (forceCredentials || this.withCredentials) ? "same-origin" : "omit"
        }).then(this.handleResponse).then(data => {
            if(data.success) {
                /*setTimeout(() => {
                    store.commit("endLoad");
                    successCallback(data);
                }, 10000);*/
                successCallback(data);
            } else if(failCallback !== undefined) {
                failCallback(data);
            } else if(store) {
                store.commit("triggerError", data.result);
            }
            if(store) { store.commit("endLoad"); }
        }).catch(error => {
            if(errorCallback !== undefined) {
                errorCallback(error);
            } else if(store) {
                store.commit("triggerError", error);
                store.commit("endLoad");
            }
        });
    }
    post(path, obj, successCallback, forceCredentials) {
        store.commit("startLoad");
        fetch(this.path + path + "/", {
            method: "POST",
            body: JSON.stringify(obj),
            headers: this.getHeaders(),
            credentials: (forceCredentials || this.withCredentials) ? "same-origin" : "omit"
        }).then(this.handleResponse).then(data => {
            if(data.success) {
                successCallback(data);
            } else {
                store.commit("triggerError", data.result);
            }
            store.commit("endLoad");
        }).catch(error => {
            store.commit("triggerError", error);
            store.commit("endLoad");
        });
    }
    delete(path, param, successCallback) {
        store.commit("startLoad");
        let paramStr = !param ? "/" : ("/" + encodeURIComponent(JSON.stringify(param)));
        fetch(this.path + path  + paramStr, {
            method: "DELETE",
            headers: this.getHeaders(),
            credentials: this.withCredentials ? "same-origin" : "omit"
        }).then(this.handleResponse).then(data => {
            if(data.success) {
                successCallback(data);
            } else {
                store.commit("triggerError", data.result);
            }
            store.commit("endLoad");
        }).catch(error => {
            store.commit("triggerError", error);
            store.commit("endLoad");
        });
    }
}
export const bee = new Beeliever(process.env.VUE_APP_API_PATH);
export const beeSecure = new Beeliever(process.env.VUE_APP_SECURE_API_PATH, true);