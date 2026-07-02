const projects = [
  {
    title: 'EcoEats APU',
    type: 'Responsive Web Design & Development',
    description: 'A web-based food waste reduction system for APU. It includes student dashboards, waste tracking, feedback, leaderboard, vendor functions and sustainability-related features.',
    cover: 'assets/images/ecoeats-dashboard.webp',
    tags: ['HTML', 'CSS', 'JavaScript', 'PHP', 'MySQL', 'WampServer'],
    features: [
      'Student dashboard with weekly and monthly waste summary',
      'Profile page with student information and statistics overview',
      'Waste log module to record food waste and CO₂ impact',
      'Feedback and rating page for students',
      'Leaderboard and challenge-related pages',
      'Vendor dashboard for event and food sharing overview'
    ],
    images: [
      { src: 'assets/images/ecoeats-dashboard.webp', alt: 'EcoEats APU student dashboard' },
      { src: 'assets/images/ecoeats-profile.webp', alt: 'EcoEats APU profile page' },
      { src: 'assets/images/ecoeats-sidebar.webp', alt: 'EcoEats APU sidebar navigation' },
      { src: 'assets/images/ecoeats-feedback-form.webp', alt: 'EcoEats APU feedback form' },
      { src: 'assets/images/ecoeats-feedback-list.webp', alt: 'EcoEats APU feedback list' },
      { src: 'assets/images/ecoeats-waste-log.webp', alt: 'EcoEats APU waste log page' },
      { src: 'assets/images/ecoeats-vendor-dashboard.webp', alt: 'EcoEats APU vendor dashboard' },
      { src: 'assets/images/ecoeats-leaderboard.webp', alt: 'EcoEats APU leaderboard page' }
    ]
  },
  {
    title: 'Trev - Asia Trip Planner',
    type: 'Capstone Project',
    description: 'A travel planning website for discovering Asian destinations, checking destination information, planning trips, managing saved trips and reading travel reviews.',
    cover: 'assets/images/trev-logo.png',
    tags: ['HTML', 'CSS', 'JavaScript', 'PHP', 'MySQL', 'WampServer'],
    features: [
      'Home page with destination discovery section',
      'Discover page with filters and destination cards',
      'Destination detail page with information and reviews',
      'Trip planner page for creating travel plans',
      'My Trips page for planned and completed trips',
      'Reviews page with user review cards'
    ],
    images: [
      { src: 'assets/images/trev-home.webp', alt: 'Trev home page' },
      { src: 'assets/images/trev-discover.webp', alt: 'Trev discover page' },
      { src: 'assets/images/trev-detail.webp', alt: 'Trev destination detail page' },
      { src: 'assets/images/trev-planner.webp', alt: 'Trev trip planner page' },
      { src: 'assets/images/trev-mytrips.webp', alt: 'Trev my trips page' },
      { src: 'assets/images/trev-reviews.webp', alt: 'Trev reviews page' }
    ]
  },
  {
    title: 'Tuition Centre Management System',
    type: 'Java Desktop Application',
    description: 'A Java Swing desktop application built in Apache NetBeans. It manages basic tuition centre functions such as student records, schedules, payments and role-based dashboards.',
    cover: 'assets/images/tuition-logo.png',
    tags: ['Java', 'Java Swing', 'Apache NetBeans', 'OOP', 'File Handling'],
    features: [
      'Role-based system for receptionist, tutor, student and admin',
      'Receptionist dashboard with menu buttons',
      'Student management page for adding, updating and deleting records',
      'Class schedule and payment-related functions',
      'Uses Java OOP concepts and text file handling',
      'Developed as a university group assignment'
    ],
    images: [
      { src: 'assets/images/java-netbeans-design.webp', alt: 'Java project design screen in NetBeans' },
      { src: 'assets/images/java-dashboard-summary.webp', alt: 'Java project dashboard summary' },
      { src: 'assets/images/java-receptionist-dashboard.webp', alt: 'Java project receptionist dashboard' },
      { src: 'assets/images/java-manage-student.webp', alt: 'Java project manage student page' }
    ]
  },
  {
    title: 'Education Management System',
    type: 'Python Console Program',
    description: 'A simple Python console-based system created to practise login, menus, file handling and basic data management. It is a small coursework-style program.',
    cover: 'assets/images/python-staff.webp',
    tags: ['Python', 'PyCharm', 'File Handling', 'Console App'],
    features: [
      'Simple login system',
      'Student, staff and admin menu options',
      'Course browsing and basic student record management',
      'Stores simple data using text files',
      'Command-line interface',
      'Created for Python programming practice'
    ],
    images: [
      { src: 'assets/images/python-staff.webp', alt: 'Python program staff menu' },
      { src: 'assets/images/python-student.webp', alt: 'Python program student menu' },
      { src: 'assets/images/python-admin.webp', alt: 'Python program admin menu' }
    ]
  }
];

const projectGrid = document.getElementById('projectGrid');
const modal = document.getElementById('projectModal');
const modalImage = document.getElementById('modalImage');
const modalTitle = document.getElementById('modalTitle');
const modalType = document.getElementById('modalType');
const modalDescription = document.getElementById('modalDescription');
const modalTags = document.getElementById('modalTags');
const modalFeatures = document.getElementById('modalFeatures');
const thumbGrid = document.getElementById('thumbGrid');
const galleryPrev = document.getElementById('galleryPrev');
const galleryNext = document.getElementById('galleryNext');
const lightbox = document.getElementById('lightbox');
const lightboxImage = document.getElementById('lightboxImage');
const lightboxClose = document.getElementById('lightboxClose');

let activeProject = null;
let activeIndex = 0;

function createProjectCards() {
  projects.forEach((project, index) => {
    const card = document.createElement('button');
    card.className = 'project-card reveal';
    card.type = 'button';
    card.setAttribute('aria-label', `View details for ${project.title}`);
    card.innerHTML = `
      <div class="project-cover">
        <img src="${project.cover}" alt="${project.title}" loading="lazy">
      </div>
      <div class="project-content">
        <span class="project-meta">${project.type}</span>
        <h3>${project.title}</h3>
        <p>${project.description}</p>
        <div class="project-tags">
          ${project.tags.slice(0, 4).map(tag => `<span>${tag}</span>`).join('')}
        </div>
        <span class="view-link">View Details →</span>
      </div>
    `;
    card.addEventListener('click', () => openProject(index));
    projectGrid.appendChild(card);
  });
}

function openProject(index) {
  activeProject = projects[index];
  activeIndex = 0;

  modalTitle.textContent = activeProject.title;
  modalType.textContent = activeProject.type;
  modalDescription.textContent = activeProject.description;
  modalTags.innerHTML = activeProject.tags.map(tag => `<span>${tag}</span>`).join('');
  modalFeatures.innerHTML = activeProject.features.map(feature => `<li>${feature}</li>`).join('');

  renderThumbs();
  updateModalImage();

  modal.classList.add('open');
  modal.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  modal.classList.remove('open');
  modal.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
}

function renderThumbs() {
  thumbGrid.innerHTML = '';
  activeProject.images.forEach((image, index) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.innerHTML = `<img src="${image.src}" alt="${image.alt}" loading="lazy">`;
    btn.addEventListener('click', () => {
      activeIndex = index;
      updateModalImage();
    });
    thumbGrid.appendChild(btn);
  });
}

function updateModalImage() {
  if (!activeProject) return;
  const image = activeProject.images[activeIndex];
  modalImage.src = image.src;
  modalImage.alt = image.alt;

  const thumbs = thumbGrid.querySelectorAll('button');
  thumbs.forEach((thumb, index) => {
    thumb.classList.toggle('active', index === activeIndex);
  });
}

function changeImage(direction) {
  if (!activeProject) return;
  activeIndex = (activeIndex + direction + activeProject.images.length) % activeProject.images.length;
  updateModalImage();
}

function openLightbox() {
  lightboxImage.src = modalImage.src;
  lightboxImage.alt = modalImage.alt;
  lightbox.classList.add('open');
  lightbox.setAttribute('aria-hidden', 'false');
}

function closeLightbox() {
  lightbox.classList.remove('open');
  lightbox.setAttribute('aria-hidden', 'true');
}

document.querySelectorAll('[data-close="modal"]').forEach(btn => {
  btn.addEventListener('click', closeModal);
});

galleryPrev.addEventListener('click', () => changeImage(-1));
galleryNext.addEventListener('click', () => changeImage(1));
modalImage.addEventListener('click', openLightbox);
lightboxClose.addEventListener('click', closeLightbox);
lightbox.addEventListener('click', (event) => {
  if (event.target === lightbox) closeLightbox();
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    closeLightbox();
    closeModal();
  }
  if (modal.classList.contains('open') && event.key === 'ArrowRight') changeImage(1);
  if (modal.classList.contains('open') && event.key === 'ArrowLeft') changeImage(-1);
});

const navToggle = document.getElementById('navToggle');
const navLinks = document.getElementById('navLinks');

navToggle.addEventListener('click', () => {
  const isOpen = navLinks.classList.toggle('open');
  navToggle.setAttribute('aria-expanded', isOpen.toString());
});

navLinks.querySelectorAll('a').forEach(link => {
  link.addEventListener('click', () => {
    navLinks.classList.remove('open');
    navToggle.setAttribute('aria-expanded', 'false');
  });
});

function revealOnScroll() {
  const reveals = document.querySelectorAll('.reveal');
  const trigger = window.innerHeight * 0.88;
  reveals.forEach(item => {
    const top = item.getBoundingClientRect().top;
    if (top < trigger) item.classList.add('show');
  });
}

function setActiveNav() {
  const sections = document.querySelectorAll('main section[id]');
  const navItems = document.querySelectorAll('.nav-links a');
  let current = 'home';

  sections.forEach(section => {
    const sectionTop = section.offsetTop - 120;
    if (window.scrollY >= sectionTop) current = section.id;
  });

  navItems.forEach(link => {
    link.classList.toggle('active', link.getAttribute('href') === `#${current}`);
  });
}

createProjectCards();
revealOnScroll();
setActiveNav();
window.addEventListener('scroll', () => {
  revealOnScroll();
  setActiveNav();
});
