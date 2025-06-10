// 假書籍資料
const books = [
    { title: "HTML 與 CSS 入門", author: "王大明", isbn: "9781234567890" },
    { title: "JavaScript 實戰", author: "李小華", isbn: "9789876543210" },
    { title: "資料結構與演算法", author: "張三", isbn: "9781122334455" },
];

function displayBooks(bookArray) {
    const bookList = document.getElementById("bookList");
    bookList.innerHTML = "";

    if (bookArray.length === 0) {
        bookList.innerHTML = "<p style='text-align:center;'>找不到相關書籍。</p>";
        return;
    }

    bookArray.forEach(book => {
        const card = document.createElement("div");
        card.className = "book-card";
        card.innerHTML = `
        <h3>${book.title}</h3>
        <p>作者：${book.author}</p>
        <p>ISBN：${book.isbn}</p>
        <button>查看詳情</button>
      `;
        bookList.appendChild(card);
    });
}

function searchBooks() {
    const keyword = document.getElementById("searchInput").value.trim().toLowerCase();
    const results = books.filter(book =>
        book.title.toLowerCase().includes(keyword) ||
        book.author.toLowerCase().includes(keyword) ||
        book.isbn.includes(keyword)
    );
    displayBooks(results);
}

// 預設顯示全部書籍
window.onload = () => displayBooks(books);