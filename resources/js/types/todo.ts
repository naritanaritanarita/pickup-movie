// TODOアイテムの型定義
export interface TodoItem {
  id: string;
  text: string;
  completed: boolean;
  createdAt: number;
}

// フィルター種別
export type FilterType = 'all' | 'active' | 'completed';
