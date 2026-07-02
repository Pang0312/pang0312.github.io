const buttons = document.querySelectorAll('.leaderboard-time-button');
const container = document.getElementById('leaderboard-container');

document.addEventListener("DOMContentLoaded" , () => {
    let buttons = document.querySelectorAll('.leaderboard-time-button');
    const params = new URLSearchParams(window.location.search);
    const currentTime = params.get('time') || 'weekly';

    buttons.forEach(btn => {
        if (btn.value.toLowerCase() === currentTime) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const time = (btn.value).toLowerCase();
            const url = new URL(window.location.href);
            url.searchParams.set('time', time);
            window.location.href = url.toString();

        })
    })
})
