import store from "src/main";
class Beeliever {
    constructor(path, withCredentials) {
        this.path = path;
        this.withCredentials = withCredentials || false;
    }
    handleResponse(response) {
        if(!response.ok) {
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
            credentials: this.withCredentials ? "same-origin" : "omit"
        }).then(this.handleResponse).then(data => {
            successCallback(data);
        }).catch(error => {
            store.state.auth = false;
            errorCallback(error);
        });
    }
    get(path, param, successCallback, failCallback, errorCallback, forceCredentials) {
        if(store) { store.loading = true; }
        let paramStr = "/";
        if(param !== undefined) {
            if(typeof param === "object") {
                paramStr = "/" + param.join("/");
            } else {
                paramStr = "/" + param;
            }
        }
        fetch(this.path + path + paramStr, {
            method: "GET",
            credentials: (forceCredentials || this.withCredentials) ? "same-origin" : "omit"
        }).then(this.handleResponse).then(data => {
            if(data.success) {
                //setTimeout(() => successCallback(data), 1000);
                successCallback(data);
            } else if(failCallback !== undefined) {
                failCallback(data);
            } else if(store) {
                store.commit("triggerError", data.result);
            }
            if(store) { store.loading = false; }
        }).catch(error => {
            if(errorCallback !== undefined) {
                errorCallback(error);
            } else if(store) {
                store.commit("triggerError", error);
                store.loading = false;
            }
        });
    }
    post(path, obj, successCallback, forceCredentials) {
        store.loading = true;
        fetch(this.path + path + "/", {
            method: "POST",
            body: JSON.stringify(obj),
            credentials: (forceCredentials || this.withCredentials) ? "same-origin" : "omit"
        }).then(this.handleResponse).then(data => {
            if(data.success) {
                successCallback(data);
            } else {
                store.commit("triggerError", data.result);
            }
            store.loading = false;
        }).catch(error => {
            store.commit("triggerError", error);
            store.loading = false;
        });
    }
    delete(path, param, successCallback) {
        store.loading = true;
        fetch(this.path + path  + "/" + (param ? param : ""), {
            method: "DELETE",
            credentials: this.withCredentials ? "same-origin" : "omit"
        }).then(this.handleResponse).then(data => {
            if(data.success) {
                successCallback(data);
            } else {
                store.commit("triggerError", data.result);
            }
            store.loading = false;
        }).catch(error => {
            store.commit("triggerError", error);
            store.loading = false;
        });
    }
}
export const bee = new Beeliever("https://www.hauntedbees.com/wstest/sonic/");
export const beeSecure = new Beeliever("https://www.hauntedbees.com/wstest/sonicSecure/", true);