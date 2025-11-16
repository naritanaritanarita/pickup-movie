import { TodoItem, FilterType } from '@/types/todo';

export class TodoList {
  private todos: TodoItem[] = [];
  private currentFilter: FilterType = 'all';
  private readonly storageKey: string = 'pickup-movie-todos';
  private readonly inputSelector: string = '#todo-input';
  private readonly listSelector: string = '#todo-list';
  private readonly addButtonSelector: string = '#add-todo-btn';
  private readonly filterButtonsSelector: string = '[data-filter]';

  constructor() {
    this.loadFromStorage();
    this.init();
  }

  private init(): void {
    // 入力フォームのイベント
    const input = document.querySelector(this.inputSelector) as HTMLInputElement;
    const addButton = document.querySelector(this.addButtonSelector);

    if (input && addButton) {
      addButton.addEventListener('click', () => this.addTodo());
      input.addEventListener('keypress', (e: KeyboardEvent) => {
        if (e.key === 'Enter') {
          this.addTodo();
        }
      });
    }

    // フィルターボタンのイベント
    const filterButtons = document.querySelectorAll(this.filterButtonsSelector);
    filterButtons.forEach(button => {
      button.addEventListener('click', (e: Event) => {
        const target = e.currentTarget as HTMLElement;
        const filter = target.getAttribute('data-filter') as FilterType;
        this.setFilter(filter);
      });
    });

    // 初期レンダリング
    this.render();
  }

  private addTodo(): void {
    const input = document.querySelector(this.inputSelector) as HTMLInputElement;
    if (!input) return;

    const text = input.value.trim();
    if (!text) return;

    const newTodo: TodoItem = {
      id: this.generateId(),
      text,
      completed: false,
      createdAt: Date.now()
    };

    this.todos.unshift(newTodo);
    this.saveToStorage();
    this.render();

    input.value = '';
  }

  private toggleTodo(id: string): void {
    const todo = this.todos.find(t => t.id === id);
    if (todo) {
      todo.completed = !todo.completed;
      this.saveToStorage();
      this.render();
    }
  }

  private deleteTodo(id: string): void {
    this.todos = this.todos.filter(t => t.id !== id);
    this.saveToStorage();
    this.render();
  }

  private setFilter(filter: FilterType): void {
    this.currentFilter = filter;
    this.updateFilterButtons();
    this.render();
  }

  private updateFilterButtons(): void {
    const filterButtons = document.querySelectorAll(this.filterButtonsSelector);
    filterButtons.forEach(button => {
      const filter = button.getAttribute('data-filter');
      if (filter === this.currentFilter) {
        button.classList.add('bg-movie-panel-active');
        button.classList.remove('bg-movie-panel');
      } else {
        button.classList.add('bg-movie-panel');
        button.classList.remove('bg-movie-panel-active');
      }
    });
  }

  private getFilteredTodos(): TodoItem[] {
    switch (this.currentFilter) {
      case 'active':
        return this.todos.filter(t => !t.completed);
      case 'completed':
        return this.todos.filter(t => t.completed);
      default:
        return this.todos;
    }
  }

  private render(): void {
    const list = document.querySelector(this.listSelector);
    if (!list) return;

    const filteredTodos = this.getFilteredTodos();

    if (filteredTodos.length === 0) {
      list.innerHTML = `
        <div class="text-center py-8 text-movie-gray">
          ${this.currentFilter === 'completed' ? 'まだ完了したTODOはありません' :
            this.currentFilter === 'active' ? 'すべてのTODOが完了しています！' :
            'TODOを追加してください'}
        </div>
      `;
      return;
    }

    list.innerHTML = filteredTodos.map(todo => this.renderTodoItem(todo)).join('');

    // イベントリスナーを再設定
    this.attachTodoItemEvents();
  }

  private renderTodoItem(todo: TodoItem): string {
    return `
      <div class="bg-movie-panel rounded-lg p-4 flex items-center gap-3 group hover:bg-movie-panel-hover transition-colors" data-todo-id="${todo.id}">
        <input
          type="checkbox"
          ${todo.completed ? 'checked' : ''}
          class="w-5 h-5 rounded border-2 border-movie-light/30 bg-movie-dark checked:bg-movie-panel-active checked:border-movie-panel-active cursor-pointer transition-all todo-checkbox"
          data-action="toggle"
        />
        <div class="flex-1 ${todo.completed ? 'line-through text-movie-muted' : 'text-movie-light'}">
          ${this.escapeHtml(todo.text)}
        </div>
        <button
          class="text-red-400 hover:text-red-300 opacity-0 group-hover:opacity-100 transition-opacity todo-delete-btn"
          data-action="delete"
          aria-label="削除"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    `;
  }

  private attachTodoItemEvents(): void {
    // チェックボックスのイベント
    const checkboxes = document.querySelectorAll('.todo-checkbox');
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', (e: Event) => {
        const target = e.currentTarget as HTMLElement;
        const todoItem = target.closest('[data-todo-id]') as HTMLElement;
        const id = todoItem?.getAttribute('data-todo-id');
        if (id) {
          this.toggleTodo(id);
        }
      });
    });

    // 削除ボタンのイベント
    const deleteButtons = document.querySelectorAll('.todo-delete-btn');
    deleteButtons.forEach(button => {
      button.addEventListener('click', (e: Event) => {
        const target = e.currentTarget as HTMLElement;
        const todoItem = target.closest('[data-todo-id]') as HTMLElement;
        const id = todoItem?.getAttribute('data-todo-id');
        if (id) {
          this.deleteTodo(id);
        }
      });
    });
  }

  private generateId(): string {
    return `${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
  }

  private escapeHtml(text: string): string {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  private loadFromStorage(): void {
    try {
      const stored = localStorage.getItem(this.storageKey);
      if (stored) {
        this.todos = JSON.parse(stored);
      }
    } catch (error) {
      console.error('Failed to load todos from storage:', error);
      this.todos = [];
    }
  }

  private saveToStorage(): void {
    try {
      localStorage.setItem(this.storageKey, JSON.stringify(this.todos));
    } catch (error) {
      console.error('Failed to save todos to storage:', error);
    }
  }
}
