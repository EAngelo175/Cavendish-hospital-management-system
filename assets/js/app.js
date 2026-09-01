document.addEventListener("DOMContentLoaded", () => {
	const menuButton = document.querySelector(".menu-toggle");
	const sidebar = document.querySelector(".sidebar");

	if (!menuButton || !sidebar) return;

	menuButton.addEventListener("click", () => {
		const isOpen = sidebar.classList.toggle("is-open");
		menuButton.setAttribute("aria-expanded", String(isOpen));
	});
});