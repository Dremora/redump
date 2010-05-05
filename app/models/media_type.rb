class MediaType < ActiveRecord::Base
  has_many :discs
  has_and_belongs_to_many :property_groups
  has_many :childs, :class_name => "MediaType", :foreign_key => "parent_id"
  belongs_to :parent, :class_name => "MediaType"
  
  attr_accessible :title, :abbreviation, :parent_id
  
  validates_length_of :title, :in => 1..255
  validates_length_of :abbreviation, :maximum => 255
  
  before_save do
    self.abbreviation = self.abbreviation.to_s
  end
  
  def self.tree(elements = self.all, root = nil)
    tree = {}
    elements.select { |element| element.parent_id == root }.each do |element|
      tree[element] = tree(elements, element.id)
    end
    tree
  end
end
