let sect = document.getElementById("Visible_Light_Flat_Earth_Observations");
let heading = sect.closest("h2");
let table = heading.nextElementSibling;

var parsed = {};
let urlAdded = -1;
if (table.tagName.toUpperCase() == "TABLE" && table.classList.contains("wikitable")) {
	parsed.columns = [];
	let cols = table.querySelectorAll("thead th");
	let ii = 0;
	for (let i=0; i < cols.length; i++) {
		parsed.columns[ii] = cols[i].innerText;
		if (cols[i].innerText == "Observation") {
			urlAdded = i;
			ii++;
			parsed.columns[ii] = "Href";
		}
		ii++;
	}
	parsed.rows = [];
	rows = table.querySelectorAll("tbody tr");
	for (let i=0; i < rows.length; i++) {
		parsed.rows[i] = [];
		cells = rows[i].querySelectorAll("td");
		let jj = 0;
		for (let j=0; j < cells.length; j++) {
			parsed.rows[i][jj] = cells[j].innerText;
			if (urlAdded >= 0 && urlAdded == j) {
				jj++;
				parsed.rows[i][jj] = "https://wiki.24-7flatearth.org" + cells[j].querySelector("a").getAttribute("href");
			}
			jj++;
		}
	}
}

var str = "";
for (let i=0; i < parsed.columns.length; i++) {
	str += '"' + parsed.columns[i] + '"';
	if (i < parsed.columns.length - 1) {
		str += ',';
	} else {
		str += '\n';
	}
}
for (let i=0; i < parsed.rows.length; i++) {
	for (let j=0; j < parsed.rows[i].length; j++) {
		str += '"' + parsed.rows[i][j] + '"';
		if (j < parsed.rows[i].length - 1) {
			str += ',';
		} else {
			str += '\n';
		}
	}
}

//console.log(str);
let elem = document.createElement("a");
elem.setAttribute("href","data:text/csv," + encodeURIComponent(str));
elem.setAttribute("download","too_far.csv");
elem.style.display = "none";
//elem.innerText = "click";
document.body.appendChild(elem);
elem.click();
document.body.removeChild(elem);

