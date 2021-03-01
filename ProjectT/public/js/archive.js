function loadContent(res) {
    console.log(res);
    let oldTraining = document.querySelector("#oldTraining");

    if(res != 0)
    {
        for(let tmp of res)
        {
            let img = document.createElement('img');
            img.src = "../images/folder.png";

            let title = document.createElement('h1');
            title.textContent = tmp.date;

            let elimina = document.createElement('img');
            elimina.src = "../images/elimina.png";
            elimina.setAttribute("class", "del");
            elimina.addEventListener("click", openModal);

            let div = document.createElement('div');
            div.setAttribute("class", "raccolta");
            div.setAttribute("data-id", tmp.id);
            div.appendChild(img);
            div.appendChild(elimina);
            div.appendChild(title);

            div.addEventListener("click", mostraContenuto);
            div.addEventListener("mouseover", mostraElimina);
            div.addEventListener("mouseout", nascondiElimina);

            oldTraining.appendChild(div);
        }
    }
    else
    {
        let text = document.createElement('h1');
        text.textContent = "There are no items to show";
        text.setAttribute("id", "empty");

        oldTraining.appendChild(text);
    }
}

function mostraContenuto(event) {
    window.location.replace("/home/" + event.currentTarget.dataset.id);
}

function mostraElimina(event) {
    let del = document.querySelectorAll(".del");
    for(let tmp of del) {
        if(tmp.parentElement == event.currentTarget) {
            tmp.id = "initial";
        }
    }
}

function nascondiElimina(event) {
    let del = document.querySelectorAll(".del");
    for(let tmp of del) {
        if(tmp.parentElement == event.currentTarget) {
            tmp.id = "";
        }
    }
}

function eliminaContenuto(event) {
    event.stopPropagation();
    console.log(event.currentTarget.dataset.id);
    removeModal();
    request("/delete", "id=" + event.currentTarget.dataset.id, loadPage);
}

function loadPage(res) {
    if(res != 1)
        alert("Errore eliminazione");

    document.querySelector("#oldTraining").innerHTML = "";
    request("/load", "", loadContent);
}

function redirectHome() {
    window.location.replace("/home");
}

function openModal(event) {
    event.stopPropagation();
    document.body.classList.add("no-scroll");
    document.querySelector("#Y").dataset.id = event.currentTarget.parentElement.dataset.id;
    document.querySelector("#modal-view").classList.remove("hidden");
}

function removeModal() {
    document.body.classList.remove("no-scroll");
    document.querySelector("#modal-view").classList.add("hidden");
}

let back = document.querySelector("#archive");
back.text = "Back";
back.addEventListener("click", redirectHome);

document.querySelector("#modal-view").addEventListener("click", removeModal);
document.querySelector("#Y").addEventListener("click", eliminaContenuto);
document.querySelector("#N").addEventListener("click", removeModal);

loadPage(1);
