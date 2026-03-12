
document.addEventListener("DOMContentLoaded", () => {
    const searchBtn = document.getElementById("searchBtn");
    const searchInput = document.getElementById("searchInput");
    const results = document.getElementById("searchResults");

    searchBtn.addEventListener("click", () => {
        let q = searchInput.value.trim();

        if (q.length === 0) {
            results.innerHTML = "<p>Please enter a vehicle name.</p>";
            results.style.display = "block";
            return;
        }

        fetch("search.php?q=" + encodeURIComponent(q))
            .then(res => res.text())
            .then(data => {
                results.innerHTML = data;
                results.style.display = "block";
                window.scrollTo({ top: 0, behavior: "smooth" });
            });
    });
});






