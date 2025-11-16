import './bootstrap';
import { MovieList } from './components/movieList';
import { MovieCarousel } from './components/movieCarousel';
import { TodoList } from './components/todoList';

// DOMのロード完了時に実行
document.addEventListener('DOMContentLoaded', () => {
  // 映画リスト機能の初期化（discover.blade.php用）
  const movieGridElement = document.getElementById('movie-grid');
  if (movieGridElement) {
    const categoryMeta = document.querySelector('meta[name="movie-category"]');
    const languageMeta = document.querySelector('meta[name="movie-language"]');
    const streamingMeta = document.querySelector('meta[name="movie-streaming"]');

    const category = categoryMeta ? categoryMeta.getAttribute('content') : '';
    const language = languageMeta ? languageMeta.getAttribute('content') : '';
    const streaming = streamingMeta ? streamingMeta.getAttribute('content') : '';

    if (category && language && streaming) {
      new MovieList({
        category,
        language,
        streaming
      });
    }
  }

  // カルーセル機能の初期化（show.blade.php用）
  const carouselElements = document.querySelectorAll('.relative.overflow-hidden');
  if (carouselElements.length > 0) {
    new MovieCarousel();
  }

  // TODO機能の初期化（todo.blade.php用）
  const todoAppElement = document.getElementById('todo-app');
  if (todoAppElement) {
    new TodoList();
  }
});
