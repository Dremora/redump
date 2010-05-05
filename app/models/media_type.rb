class MediaType < ActiveRecord::Base
  has_many :discs
  has_and_belongs_to_many :property_groups
  has_many :childs, :class_name => "MediaType", :foreign_key => "parent_id"
  belongs_to :parent, :class_name => "MediaType"

  @@formats = ["CD", "DVD"]
    
  attr_protected :created_at, :updated_at
  
  validates_inclusion_of :format, :in => @@formats
  validates_length_of :title, :in => 1..255
  validates_length_of :abbreviation, :maximum => 255
  
  before_save do
    self.abbreviation = self.abbreviation.to_s
  end
  
  # make protected
  def self.tree_branch(elements, id)
    new_elements = elements.select { |element| element.parent_id == id }
    branch = Hash.new
    new_elements.each do |element|
      logger.debug element
      branch[element] = tree_branch(elements, element.id)
    end
    branch
  end
  
  def self.tree
    elements = tree_branch(self.all, nil)
  end
end
